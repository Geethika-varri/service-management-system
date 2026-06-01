<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ServiceRequestService;
use Illuminate\Http\Request;
use App\Models\User;

class ServiceRequestAdminController extends Controller
{
    protected $service;

    public function __construct(ServiceRequestService $service)
    {
        $this->service = $service;
    }

    public function allRequests()
    {
        $requests = $this->service->getFilteredRequests();

        // API support (DO NOT REMOVE)
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $requests
            ]);
        }

        // UI support
        $technicians = User::where('role', 'technician')->get();

        return view('admin.requests.index', compact('requests', 'technicians'));
    }

    public function forceAssign($id, Request $request)
    {
        $this->authorize('assign', \App\Models\ServiceRequest::class);

        $technicianId = $request->input('technician_id');

        $result = $this->service->forceAssign($id, $technicianId);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function forceComplete($id)
    {
        $this->authorize('complete', \App\Models\ServiceRequest::class);

        $result = $this->service->forceComplete($id);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}