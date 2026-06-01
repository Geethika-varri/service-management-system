<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\RequestUIService;
use App\Services\RequestSLAService;
use App\Services\SLAAnalyticsService;

class DashboardController extends Controller
{
    public function admin(SLAAnalyticsService $analyticsService)
    {
        $filters = []; // admin = global (no filters)

        $totalRequests = $analyticsService->getTotalRequests($filters);
        $completedRequests = $analyticsService->getCompletedRequests($filters);
        $delayedRequests = $analyticsService->getDelayedRequests($filters);
        $totalBreaches = $analyticsService->getTotalBreaches($filters);

        return view('dashboard.admin', compact(
            'totalRequests',
            'completedRequests',
            'delayedRequests',
            'totalBreaches'
        ));
    }

    public function manager(
        RequestUIService $uiService,
        RequestSLAService $slaService,
        SLAAnalyticsService $analyticsService
    ) {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }
        $filters = [
            'from' => request('from'),
            'to' => request('to'),
            'technician_id' => request('technician_id'),
            'status' => request('status'),
        ];

        $query = ServiceRequest::with(['customer', 'technician']);

        if ($filters['from']) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if ($filters['to']) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if ($filters['technician_id']) {
            $query->where('technician_id', $filters['technician_id']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $allRequests = $query->get();

        $statusPriority = [
            'pending' => 1,
            'assigned' => 2,
            'in_progress' => 3,
            'completed' => 4,
        ];

        $sorted = $allRequests
            ->sortByDesc('created_at') // latest first
            ->sortBy(fn($req) => $statusPriority[$req->status] ?? 99) // then by status
            ->values();

        // Pagination fix
        $page = request()->get('page', 1);
        $perPage = 10;

        $requests = new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        foreach ($requests as $req) {
            $req->actions = $uiService->getAvailableActions($req, $user);
            $req->badge = $uiService->getStatusBadge($req->status);

            // SLA DATA (ADD THESE 3 LINES)
            $req->duration = $slaService->getDuration($req);
            $req->liveDuration = $slaService->getLiveDuration($req);
            $req->isDelayed = $slaService->isDelayed($req);
        }
        // SLA ANALYTICS (GLOBAL DATA)
        $totalRequests = $analyticsService->getTotalRequests($filters);
        $completedRequests = $analyticsService->getCompletedRequests($filters);
        $delayedRequests = $analyticsService->getDelayedRequests($filters);
        $totalBreaches = $analyticsService->getTotalBreaches($filters);
        $avgResolutionTime = $analyticsService->getAverageResolutionTime($filters);
        $avgDelayTime = $analyticsService->getAverageDelayTime($filters);

        $technicians = \App\Models\User::where('role', 'technician')->get();

        return view('dashboard.manager', compact(
            'requests',
            'technicians',
            'totalRequests',
            'completedRequests',
            'delayedRequests',
            'totalBreaches',
            'avgResolutionTime',
            'avgDelayTime',
            'filters'
        ));
    }

    public function technician()
    {
        $requests = ServiceRequest::with(['customer'])
            ->where('technician_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('dashboard.technician', compact('requests'));
    }

    public function customer()
    {
        $requests = ServiceRequest::with(['technician'])
            ->where('customer_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('dashboard.customer', compact('requests'));
    }
}
