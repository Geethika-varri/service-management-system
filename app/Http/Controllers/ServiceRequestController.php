<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\AssignServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServiceRequestController extends Controller
{
    protected $service;

    public function __construct(ServiceRequestService $service)
    {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER: CREATE (FORM VIEW)
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        // ✅ Policy check
        $this->authorize('create', ServiceRequest::class);

        return view('customer.requests.create');
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER: STORE
    |--------------------------------------------------------------------------
    */
    public function store(StoreServiceRequest $request)
    {
        try {
            // ✅ Policy check
            $this->authorize('create', ServiceRequest::class);

            $this->service->create($request->validated());

            return redirect()->route('customer.dashboard')
                ->with('success', 'Request Created');

        } catch (AuthorizationException $e) {

            return back()->with('error', 'Unauthorized action');

        } catch (\Exception $e) {

            return back()->with('error', 'Something went wrong');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MANAGER: ASSIGN
    |--------------------------------------------------------------------------
    */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id'
        ]);

        try {
            // ✅ Fetch request
            $serviceRequest = ServiceRequest::findOrFail($id);

            // ✅ Policy check
            $this->authorize('assign', $serviceRequest);

            // ✅ Business logic
            $this->service->assign(
                $serviceRequest,
                $request->input('technician_id')
            );

            return back()->with('success', 'Technician Assigned');

        } catch (AuthorizationException $e) {

            return back()->with('error', 'Unauthorized action');

        } catch (ModelNotFoundException $e) {

            return back()->with('error', 'Service request not found');

        } catch (\Exception $e) {

            return back()->with('error', 'Something went wrong');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NOTE: Technician actions moved to TechnicianController
    |--------------------------------------------------------------------------
    */
}