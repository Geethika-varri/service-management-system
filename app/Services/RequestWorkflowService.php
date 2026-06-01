<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class RequestWorkflowService
{
    private array $transitions = [
        'pending' => ['assigned'],
        'assigned' => ['in_progress'],
        'in_progress' => ['completed'],
        'completed' => ['reopened'],
        'reopened' => ['assigned'],
        'rejected' => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->transitions[$from] ?? []);
    }

    public function transition(ServiceRequest $request, string $to, int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => 'User not found'
            ]);
        }

        // ROLE VALIDATION
        if ($user->role === 'technician') {

            if (!in_array($to, ['in_progress', 'completed'])) {
                throw ValidationException::withMessages([
                    'role' => 'Unauthorized'
                ]);
            }

            if ($request->assigned_to !== $user->id) {
                throw ValidationException::withMessages([
                    'ownership' => 'Not your task'
                ]);
            }

        } elseif ($user->role === 'manager') {

            if (!in_array($to, ['assigned', 'reopened', 'rejected'])) {
                throw ValidationException::withMessages([
                    'role' => 'Unauthorized'
                ]);
            }

        } elseif ($user->role === 'admin') {
            // Admin has full access

        } else {

            throw ValidationException::withMessages([
                'status' => 'Unauthorized role for status transitions'
            ]);
        }

        $from = $request->status;

        // TRANSITION VALIDATION
        if (!$this->canTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => "Invalid transition: $from → $to"
            ]);
        }

        // APPLY SIDE EFFECTS (SLA TIMESTAMPS)
        $this->applySideEffects($request, $from, $to);

        // UPDATE STATUS
        $request->status = $to;
        $request->save();

        // LOG TRANSITION
        $request->statusLogs()->create([
            'request_id' => $request->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $userId,
        ]);
    }

    private function applySideEffects(ServiceRequest $request, string $from, string $to): void
    {
        // START SLA TIMER
        if ($to === 'in_progress') {

            // Case 1: First time start
            if (!$request->started_at) {
                $request->started_at = Carbon::now();
            }

            // Case 2: Reopened → restart SLA
            if ($from === 'assigned' && $request->completed_at !== null) {
                $request->started_at = Carbon::now();
            }
        }

        // COMPLETE SLA
        if ($to === 'completed') {

            if (!$request->completed_at) {
                $request->completed_at = Carbon::now();
            }
        }

        // REOPEN → reset completion only
        if ($to === 'reopened') {
            $request->completed_at = null;
        }
    }
}