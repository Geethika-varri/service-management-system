<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;


class ServiceRequestService
{
    /*
    |------------------------------------------------------------------
    | GET TECHNICIAN REQUESTS
    |------------------------------------------------------------------
    */
    public function getTechnicianRequests($userId)
    {
        return ServiceRequest::with(['customer'])
            ->where('assigned_to', $userId)
            ->get()
            ->sortBy(function (ServiceRequest $request) {

                $status = $request->getDynamicSlaStatus();

                return match ($status) {
                    'breached' => 1,
                    'nearing_breach' => 2,
                    'on_time' => 3,
                    default => 4,
                };
            })
            ->values();
    }

    /*
    |------------------------------------------------------------------
    | CREATE REQUEST (Customer)
    |------------------------------------------------------------------
    */
    public function create(array $data): ServiceRequest
    {
        return DB::transaction(function () use ($data) {

            if (!isset($data['priority']) || !in_array($data['priority'], ['low', 'medium', 'high'])) {
                throw new HttpException(422, 'Invalid priority value');
            }

            $data['customer_id'] = auth()->id();
            $data['status'] = 'pending';
            $data['sla_hours'] = $this->getSlaHours($data['priority']);

            return ServiceRequest::create($data);
        });
    }

    /*
    |------------------------------------------------------------------
    | ASSIGN REQUEST (Manager)
    |------------------------------------------------------------------
    */
    public function assign(ServiceRequest $request, int $technicianId): ServiceRequest
    {
        return DB::transaction(function () use ($request, $technicianId) {

            // 🔒 Lock the row to prevent race conditions
            $lockedRequest = ServiceRequest::where('id', $request->id)
                ->lockForUpdate()
                ->firstOrFail();

            $idempotencyKey = request()->header('Idempotency-Key');

            if ($idempotencyKey) {
                $exists = ActivityLog::where('idempotency_key', $idempotencyKey)->exists();

                if ($exists) {
                    return $lockedRequest;
                }
            }

            // ✅ Idempotency: if already assigned to same technician, return safely
            if ($lockedRequest->assigned_to === $technicianId) {
                return $lockedRequest;
            }

            if (!$lockedRequest->canTransitionTo('assigned') || $lockedRequest->assigned_to !== null) {
                throw new HttpException(400, 'Request already assigned or invalid state');
            }

            $lockedRequest->update([
                'assigned_to' => $technicianId,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            ActivityLog::create([
                'service_request_id' => $lockedRequest->id,
                'user_id' => auth()->id(),
                'action' => 'assigned',
                'idempotency_key' => $idempotencyKey,
            ]);

            return $lockedRequest;
        });
    }

    /*
    |------------------------------------------------------------------
    | START WORK (Technician)
    |------------------------------------------------------------------
    */
    public function startWork($requestId, $userId): ServiceRequest
    {
        return DB::transaction(function () use ($requestId, $userId) {

            $request = ServiceRequest::where('id', $requestId)
                ->lockForUpdate()
                ->firstOrFail();
            $idempotencyKey = request()->header('Idempotency-Key');

            if ($idempotencyKey) {
                $exists = ActivityLog::where('idempotency_key', $idempotencyKey)->exists();

                if ($exists) {
                    return $request;
                }
            }

            // Idempotency: already started
            if ($request->status === 'in_progress') {
                return $request;
            }

            // Ownership check
            if ($request->assigned_to !== $userId) {
                throw new HttpException(403, 'Unauthorized action');
            }

            // Strict state validation
            if (!$request->canTransitionTo('in_progress')) {
                throw new HttpException(400, 'Invalid status transition');
            }

            $request->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            ActivityLog::create([
                'service_request_id' => $request->id,
                'user_id' => $userId,
                'action' => 'started',
                'idempotency_key' => $idempotencyKey,
            ]);

            return $request;
        });
    }

    /*
    |------------------------------------------------------------------
    | COMPLETE REQUEST (Technician)
    |------------------------------------------------------------------
    */
    public function completeWork($requestId, $userId): ServiceRequest
    {
        return DB::transaction(function () use ($requestId, $userId) {

            $request = ServiceRequest::where('id', $requestId)
                ->lockForUpdate()
                ->firstOrFail();

            $idempotencyKey = request()->header('Idempotency-Key');

            if ($idempotencyKey) {
                $exists = ActivityLog::where('idempotency_key', $idempotencyKey)->exists();

                if ($exists) {
                    return $request;
                }
            }

            // Idempotency: already completed
            if ($request->status === 'completed') {
                return $request;
            }

            if ($request->assigned_to !== $userId) {
                throw new HttpException(403, 'Unauthorized action');
            }

            if (!$request->canTransitionTo('completed')) {
                throw new HttpException(400, 'Invalid status transition');
            }

            $completedAt = now();
            $isBreached = $this->checkSlaBreach($request, $completedAt);

            $request->update([
                'status' => 'completed',
                'completed_at' => $completedAt,
                'is_sla_breached' => $isBreached,
            ]);

            ActivityLog::create([
                'service_request_id' => $request->id,
                'user_id' => $userId ?? auth()->id(),
                'action' => 'completed',
                'idempotency_key' => $idempotencyKey,
            ]);

            return $request;
        });
    }

    /*
    |------------------------------------------------------------------
    | REOPEN REQUEST
    |------------------------------------------------------------------
    */
    public function reopenRequest($requestId, $userId): ServiceRequest
    {
        return DB::transaction(function () use ($requestId, $userId) {

            $request = ServiceRequest::where('id', $requestId)
                ->lockForUpdate()
                ->firstOrFail();

            $idempotencyKey = request()->header('Idempotency-Key');

            if ($idempotencyKey) {
                $exists = ActivityLog::where('idempotency_key', $idempotencyKey)->exists();

                if ($exists) {
                    return $request;
                }
            }

            // Idempotency: already reopened
            if ($request->status === 'reopened') {
                return $request;
            }

            if (!$request->canTransitionTo('reopened')) {
                throw new HttpException(400, 'Invalid status transition');
            }

            $request->update([
                'status' => 'reopened',
                'started_at' => null,
                'completed_at' => null,
                'is_sla_breached' => false,
            ]);

            ActivityLog::create([
                'service_request_id' => $request->id,
                'user_id' => $userId,
                'action' => 'reopened',
                'idempotency_key' => $idempotencyKey,
            ]);

            return $request;
        });
    }

    /*
    |------------------------------------------------------------------
    | SLA LOGIC
    |------------------------------------------------------------------
    */
    private function getSlaHours(string $priority): int
    {
        return match ($priority) {
            'low' => 48,
            'medium' => 24,
            'high' => 8,
            default => 24,
        };
    }

    private function checkSlaBreach(ServiceRequest $request, Carbon $completedAt): bool
    {
        $deadline = $request->getSlaDeadline();

        if (!$deadline) {
            return false;
        }

        return $completedAt->greaterThan($deadline);
    }
    public function getManagerDashboardStats(): array
    {
        $requests = ServiceRequest::all();

        return [
            'total' => $requests->count(),

            'pending' => $requests->where('status', 'pending')->count(),
            'assigned' => $requests->where('status', 'assigned')->count(),
            'in_progress' => $requests->where('status', 'in_progress')->count(),
            'completed' => $requests->where('status', 'completed')->count(),

            'sla_breached' => $requests->filter(function ($request) {
                return $request->getDynamicSlaStatus() === 'breached';
            })->count(),
        ];
    }
    public function getFilteredRequests(?string $status = null, ?string $sla = null)
    {
        $requests = ServiceRequest::with(['customer', 'technician'])->get();

        // Filter by status
        if ($status) {
            $requests = $requests->where('status', $status);
        }

        // Filter by SLA
        if ($sla) {
            $requests = $requests->filter(function ($request) use ($sla) {
                return $request->getDynamicSlaStatus() === $sla;
            });
        }

        return $requests->values();
    }
    public function forceAssign(int $requestId, int $technicianId): ServiceRequest
{
    return DB::transaction(function () use ($requestId, $technicianId) {

        $request = ServiceRequest::where('id', $requestId)
            ->lockForUpdate()
            ->firstOrFail();

        $request->update([
            'assigned_to' => $technicianId,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        ActivityLog::create([
            'service_request_id' => $request->id,
            'user_id' => auth()->id(),
            'action' => 'force_assigned',
        ]);

        return $request;
    });
}
public function forceComplete(int $requestId): ServiceRequest
{
    return DB::transaction(function () use ($requestId) {

        $request = ServiceRequest::where('id', $requestId)
            ->lockForUpdate()
            ->firstOrFail();

        $request->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        ActivityLog::create([
            'service_request_id' => $request->id,
            'user_id' => auth()->id(),
            'action' => 'force_completed',
        ]);

        return $request;
    });
}
}