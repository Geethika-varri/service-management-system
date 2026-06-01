<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Carbon\Carbon;
use App\Models\SLABreach;



class RequestSLAService
{
    /**
     * Get completed duration in minutes
     */
    public function getDuration(ServiceRequest $request): ?int
    {
        // Case: Not started
        if (!$request->started_at) {
            return null;
        }

        // Case: Not completed
        if (!$request->completed_at) {
            return null;
        }

        $start = Carbon::parse($request->started_at);
        $end   = Carbon::parse($request->completed_at);

        return $start->diffInMinutes($end);
    }

    /**
     * Get live duration (for in-progress requests)
     */
    public function getLiveDuration(ServiceRequest $request): ?int
    {
        // If not started → nothing to calculate
        if (!$request->started_at) {
            return null;
        }

        // If already completed → no live duration
        if ($request->completed_at) {
            return null;
        }

        $start = Carbon::parse($request->started_at);
        $now   = Carbon::now();

        return $start->diffInMinutes($now);
    }
    protected function logBreach(ServiceRequest $request, int $duration): void
    {
        $requestId = $request->getKey();

        $exists = SLABreach::where('service_request_id', $requestId)->exists();

        if (!$exists) {
            SLABreach::create([
                'service_request_id' => $requestId,
                'duration' => $duration,
            ]);
        }
    }

    /**
     * Check if request is delayed
     */


    public function isDelayed(ServiceRequest $request, ?int $threshold = null): bool
    {
        $threshold = $threshold ?? config('sla.threshold_minutes');

        $duration = $this->getDuration($request);

        if ($duration !== null) {
            $isDelayed = $duration > $threshold;

            if ($isDelayed) {
                $this->logBreach($request, $duration);
            }

            return $isDelayed;
        }

        $liveDuration = $this->getLiveDuration($request);

        if ($liveDuration !== null) {
            $isDelayed = $liveDuration > $threshold;

            if ($isDelayed) {
                $this->logBreach($request, $liveDuration);
            }

            return $isDelayed;
        }

        return false;
    }
}
