@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<h2 class="section-title">System Overview</h2>

<div class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    <div class="card">
        <p class="text-sm text-gray-500">Total Requests</p>
        <p class="text-2xl font-bold">{{ $totalRequests }}</p>
    </div>

    <div class="card">
        <p class="text-sm text-gray-500">Completed</p>
        <p class="text-2xl font-bold">{{ $completedRequests }}</p>
    </div>

    <div class="card">
        <p class="text-sm text-gray-500">Delayed</p>
        <p class="text-2xl font-bold">{{ $delayedRequests }}</p>
    </div>

    <div class="card">
        <p class="text-sm text-gray-500">SLA Breaches</p>
        <p class="text-2xl font-bold">{{ $totalBreaches }}</p>
    </div>

</div>

@endsection