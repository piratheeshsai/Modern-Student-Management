<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $apiToken;
    private $senderId;
    private $apiUrl;

    public function __construct()
    {
        $this->apiToken = config('services.textlk.api_token');
        $this->senderId = config('services.textlk.sender_id');
        $this->apiUrl = config('services.textlk.api_url');
    }

    /**
     * Send SMS to a single recipient with logging
     */
    public function sendSms(string $phoneNumber, string $message, $enrollmentId = null, $studentId = null): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        // Create initial log entry in notifications table
        $notification = Notification::create([
            'user_id' => 1, // Default user ID
            'student_id' => $studentId,
            'type' => 'enrollment_sms',
            'message' => $message,
            'status' => 'pending',
            'phone_number' => $formattedPhone,
            'response_data' => json_encode([
                'enrollment_id' => $enrollmentId,
                'attempt' => 1
            ])
        ]);

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiUrl, [
                'recipient' => $formattedPhone,
                'sender_id' => $this->senderId,
                'message' => $message,
            ]);

            $success = $response->successful();

            // Update notification with result
            $notification->update([
                'status' => $success ? 'sent' : 'failed',
                'sent_at' => $success ? now() : null,
                'response_data' => json_encode([
                    'enrollment_id' => $enrollmentId,
                    'status' => $response->status(),
                    'success' => $success
                ])
            ]);

            if ($success) {
                Log::info('SMS sent successfully', [
                    'phone' => $formattedPhone,
                    'enrollment_id' => $enrollmentId,
                    'notification_id' => $notification->id
                ]);
                return true;
            }

            Log::error('SMS sending failed', [
                'phone' => $formattedPhone,
                'status' => $response->status(),
                'notification_id' => $notification->id
            ]);
            return false;

        } catch (\Exception $e) {
            // Update notification with error
            $notification->update([
                'status' => 'failed',
                'response_data' => json_encode([
                    'enrollment_id' => $enrollmentId,
                    'error' => substr($e->getMessage(), 0, 100)
                ])
            ]);

            Log::error('SMS sending exception', [
                'phone' => $formattedPhone,
                'error' => $e->getMessage(),
                'notification_id' => $notification->id
            ]);
            return false;
        }
    }

    /**
     * Format phone number for Sri Lankan numbers
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any spaces, dashes, or other characters
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Handle Sri Lankan numbers
        if (substr($phone, 0, 1) === '0') {
            // Convert 0771234567 to +94771234567
            $phone = '+94' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) === '94') {
            // Convert 94771234567 to +94771234567
            $phone = '+' . $phone;
        } elseif (substr($phone, 0, 3) !== '+94') {
            // Assume it's a local number without 0
            $phone = '+94' . $phone;
        }

        return $phone;
    }

    /**
     * Generate enrollment confirmation message
     */
    public function generateEnrollmentMessage($student, $course, $enrollment): string
    {
        $studentName = $student->first_name . ' ' . $student->last_name;
        $courseName = $course->name;
        $enrollmentDate = date('M d, Y', strtotime($enrollment->enrollment_date));

        return "Dear {$studentName}, your enrollment for {$courseName} has been confirmed on {$enrollmentDate}. Welcome to JMS Lanka!";
    }
}
