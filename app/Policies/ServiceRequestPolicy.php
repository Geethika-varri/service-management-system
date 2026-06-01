<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceRequest;


class ServiceRequestPolicy
{
    public function before(User $user, string $ability)
    {
        return $user->role === 'admin' ? true : null;
    }
    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    public function view(User $user, ServiceRequest $request): bool
    {
        // Manager → full access (Admin handled in before())
        if ($user->role === 'manager') {
            return true;
        }

        // Customer → only their own requests
        if ($user->role === 'customer') {
            return $request->customer_id === $user->id;
        }

        // Technician → only assigned
        if ($user->role === 'technician') {
            return $request->assigned_to === $user->id;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE (Customer only)
    |--------------------------------------------------------------------------
    */
    public function create(User $user): bool
    {
        return $user->role === 'customer';
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN (Manager only + pending) — Admin handled in before()
    |--------------------------------------------------------------------------
    */
    public function assign(User $user, ServiceRequest $request): bool
    {
        return $user->role === 'manager'
            && $request->status === 'pending';
    }

    /*
    |--------------------------------------------------------------------------
    | START (Technician only)
    |--------------------------------------------------------------------------
    */
    public function start(User $user, ServiceRequest $request): bool
    {
        return $user->role === 'technician'
            && $request->assigned_to === $user->id
            && $request->status === 'assigned';
    }

    /*
    |--------------------------------------------------------------------------
    | COMPLETE (Technician only)
    |--------------------------------------------------------------------------
    */
    public function complete(User $user, ServiceRequest $request): bool
    {
        return $user->role === 'technician'
            && $request->assigned_to === $user->id
            && $request->status === 'in_progress';
    }

    /*
    |--------------------------------------------------------------------------
    | REOPEN
    |--------------------------------------------------------------------------
    */
   public function reopen(User $user, ServiceRequest $request): bool
{
    if ($user->role === 'manager') {
        return $request->status === 'completed';
    }

    if ($user->role === 'customer') {
        return $request->customer_id === $user->id
            && $request->status === 'completed';
    }

    return false;
}
}