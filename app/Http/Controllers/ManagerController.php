<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;

use App\Services\ServiceRequestService;

class ManagerController extends Controller
{
    

public function dashboard(Request $request, ServiceRequestService $service)
{
    $stats = $service->getManagerDashboardStats();
    $technicians = User::where('role', 'technician')->get();
    $status = $request->query('status');
    $sla = $request->query('sla');

    $requests = $service->getFilteredRequests($status, $sla);

    return view('manager.dashboard', compact('stats', 'requests', 'status', 'sla', 'technicians'));
}
}