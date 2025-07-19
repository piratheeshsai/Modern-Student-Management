<?php

namespace App\Listeners;

use App\Events\EnrollmentCreated;
use App\Jobs\SendEnrollmentSms;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendEnrollmentSmsListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EnrollmentCreated $event): void
    {
        // ✅ PREVENT DUPLICATE JOB DISPATCH
        $lockKey = "sms_dispatch_enrollment_{$event->enrollment->id}";

        if (Cache::has($lockKey)) {
            Log::info('SMS job already dispatched for this enrollment, skipping', [
                'enrollment_id' => $event->enrollment->id
            ]);
            return;
        }

        // ✅ Set lock for 30 seconds to prevent duplicate dispatch
        Cache::put($lockKey, true, now()->addSeconds(30));

        Log::info('Enrollment created, dispatching SMS job', [
            'enrollment_id' => $event->enrollment->id
        ]);

        // Dispatch SMS job with 5 second delay
        SendEnrollmentSms::dispatch($event->enrollment)
            ->delay(now()->addSeconds(5));
    }
}
