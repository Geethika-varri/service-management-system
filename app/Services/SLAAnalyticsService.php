<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\SLABreach;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SLAAnalyticsService
{
    /**
     * Total number of requests
     */
    public function getTotalRequests(array $filters = []): int
    {
        $query = ServiceRequest::query();

        $this->applyFilters($query, $filters);

        return $query->count();
    }

    /**
     * Total completed requests
     */
    public function getCompletedRequests(array $filters = []): int
    {
        $query = ServiceRequest::whereNotNull('completed_at');

        $this->applyFilters($query, $filters);

        return $query->count();
    }

    /**
     * Total delayed requests
     * (based on SLA threshold)
     */
    public function getDelayedRequests(array $filters = []): int
    {
        $threshold = config('sla.threshold_minutes');

        $query = ServiceRequest::whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, started_at, completed_at) > ?', [$threshold]);

        $this->applyFilters($query, $filters);

        return $query->count();
    }

    /**
     * Total SLA breaches (from table)
     */
    public function getTotalBreaches(array $filters = []): int
    {
        $query = SLABreach::query()
            ->join('service_requests', 'sla_breaches.service_request_id', '=', 'service_requests.id');

        $this->applyFilters($query, $filters);

        return $query->count();
    }

    /**
     * Average resolution time (in minutes)
     */
    public function getAverageResolutionTime(array $filters = []): ?float
    {
        $query = ServiceRequest::whereNotNull('started_at')
            ->whereNotNull('completed_at');

        $this->applyFilters($query, $filters);

        $avg = $query
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_time')
            ->value('avg_time');

        return $avg ? round($avg, 2) : null;
    }

    /**
     * Average delay time (only breached requests)
     */
    public function getAverageDelayTime(array $filters = []): ?float
    {
        $query = SLABreach::query()
            ->join('service_requests', 'sla_breaches.service_request_id', '=', 'service_requests.id');

        $this->applyFilters($query, $filters);

        $avg = $query->avg('sla_breaches.duration');

        return $avg ? round($avg, 2) : null;
    }
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (!empty($filters['technician_id'])) {
            $query->where('technician_id', $filters['technician_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
