<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\ServiceRequest;
use App\Policies\ServiceRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ServiceRequest::class => ServiceRequestPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}