<?php

namespace App\Enums;

class RequestStatus
{
    const PENDING = 'pending';
    const ASSIGNED = 'assigned';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';

    public static function transitions()
    {
        return [
            self::ASSIGNED => [self::IN_PROGRESS],
            self::IN_PROGRESS => [self::COMPLETED],
        ];
    }
}