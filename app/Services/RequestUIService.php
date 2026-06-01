<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\User;

class RequestUIService
{
    public function getAvailableActions(ServiceRequest $request, User $user): array
{
    $actions = [];

    switch ($request->status) {

        case 'pending':
            if ($user->role === 'manager') {
                $actions[] = 'assign';
            }
            break;

        case 'assigned':
            if ($user->role === 'technician' && $request->technician_id === $user->id) {
                $actions[] = 'start';
            }
            break;

        case 'in_progress':
            if ($user->role === 'technician' && $request->technician_id === $user->id) {
                $actions[] = 'complete';
            }
            break;

        case 'completed':
            if ($user->role === 'manager') {
                $actions[] = 'reopen';
            }
            break;

        case 'reopened':
            if ($user->role === 'manager') {
                $actions[] = 'assign';
            }
            break;
    }

    return $actions;
}

    public function getStatusBadge(string $status): string
    {
        return match ($status) {
            'pending' => 'secondary',
            'assigned' => 'primary',
            'in_progress' => 'warning',
            'completed' => 'success',
            'reopened' => 'warning',
            'rejected' => 'danger',
            default => 'dark'
        };
    }

    public function getProgressSteps(string $status): array
    {
        // For non-linear or inactive states, show no progress
        if (in_array($status, ['pending', 'reopened', 'rejected'])) {
            return [
                'assigned' => false,
                'in_progress' => false,
                'completed' => false,
            ];
        }

        return [
            'assigned' => in_array($status, ['assigned', 'in_progress', 'completed']),
            'in_progress' => in_array($status, ['in_progress', 'completed']),
            'completed' => $status === 'completed',
        ];
    }
}