<?php

namespace App\Events;

use App\Models\Enrollment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $enrollment;

    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }
}
