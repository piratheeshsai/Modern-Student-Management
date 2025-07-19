<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\Notification;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendEnrollmentSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $enrollment;
    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
        $this->onQueue('sms');
    }

    public function handle(SmsService $smsService): void
    {
        // ✅ PREVENT DUPLICATE SMS - Check if already processed
        $lockKey = "sms_enrollment_{$this->enrollment->id}";

        if (Cache::has($lockKey)) {
            Log::info('SMS already sent for this enrollment, skipping', [
                'enrollment_id' => $this->enrollment->id
            ]);
            return;
        }

        // ✅ Also check notifications table for existing SMS
        $existingSms = Notification::where('student_id', $this->enrollment->student_id)
            ->where('type', 'enrollment_sms')
            ->where('response_data', 'like', '%"enrollment_id":' . $this->enrollment->id . '%')
            ->where('status', '!=', 'failed')
            ->exists();

        if ($existingSms) {
            Log::info('SMS already exists in notifications for this enrollment, skipping', [
                'enrollment_id' => $this->enrollment->id
            ]);
            return;
        }

        // ✅ Set lock to prevent duplicate processing
        Cache::put($lockKey, true, now()->addMinutes(10));

        Log::info('SMS Job started processing', [
            'enrollment_id' => $this->enrollment->id,
            'job_id' => $this->job->getJobId() ?? 'unknown'
        ]);

        // Load relationships
        $this->enrollment->load(['student', 'course']);

        $student = $this->enrollment->student;
        $course = $this->enrollment->course;

        Log::info('SMS Job loaded data', [
            'student_id' => $student->id,
            'student_mobile' => $student->mobile ?? 'none',
            'course_name' => $course->name
        ]);

        // Check if student has a valid phone number
        if (!$student->mobile || empty(trim($student->mobile))) {
            Log::warning('SMS Job: No mobile number', [
                'enrollment_id' => $this->enrollment->id,
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name
            ]);

            // Log to notifications table
            Notification::create([
                'user_id' => 1, // Default user ID
                'student_id' => $student->id,
                'type' => 'enrollment_sms',
                'message' => 'Cannot send enrollment SMS - student has no mobile number',
                'status' => 'failed',
                'phone_number' => null,
                'response_data' => json_encode([
                    'error' => 'No mobile number',
                    'enrollment_id' => $this->enrollment->id
                ])
            ]);

            return;
        }

        Log::info('SMS Job: Attempting to send SMS', [
            'mobile' => $student->mobile,
            'enrollment_id' => $this->enrollment->id
        ]);

        // Generate message
        $message = $smsService->generateEnrollmentMessage($student, $course, $this->enrollment);

        // Send SMS
        $sent = $smsService->sendSms(
            $student->mobile,
            $message,
            $this->enrollment->id,
            $student->id
        );

        if (!$sent) {
            // ✅ Remove lock on failure so it can retry
            Cache::forget($lockKey);

            Log::error('SMS Job: SMS sending failed', [
                'enrollment_id' => $this->enrollment->id,
                'student_id' => $student->id,
                'mobile' => $student->mobile,
                'attempt' => $this->attempts()
            ]);

            if ($this->attempts() >= $this->tries) {
                Log::error('SMS sending permanently failed after all retries', [
                    'enrollment_id' => $this->enrollment->id,
                    'total_attempts' => $this->attempts()
                ]);
            }

            throw new \Exception('SMS sending failed');
        }

        Log::info('SMS Job: SMS sent successfully', [
            'enrollment_id' => $this->enrollment->id,
            'student_id' => $student->id,
            'mobile' => $student->mobile,
            'attempt' => $this->attempts()
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        // ✅ Remove lock when job permanently fails
        $lockKey = "sms_enrollment_{$this->enrollment->id}";
        Cache::forget($lockKey);

        Log::error('SMS job permanently failed', [
            'enrollment_id' => $this->enrollment->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        $student = $this->enrollment->student;

        Notification::create([
            'user_id' => 1, // Default user ID
            'student_id' => $student->id ?? null,
            'type' => 'enrollment_sms',
            'message' => 'SMS failed after all retry attempts',
            'status' => 'failed',
            'phone_number' => $student->mobile ?? null,
            'response_data' => json_encode([
                'enrollment_id' => $this->enrollment->id,
                'error' => 'Permanent failure',
                'attempts' => $this->attempts()
            ])
        ]);
    }
}
