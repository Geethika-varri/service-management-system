<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TechnicianController extends Controller
{
    protected $service;

    public function __construct(ServiceRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * Show technician assigned requests
     */
    public function index()
    {
        $requests = $this->service->getTechnicianRequests(Auth::id());

        return view('technician.requests.index', compact('requests'));
    }

    /**
     * Start work (assigned → in_progress)
     */
    public function startWork($id)
    {
        try {
            // Fetch request
            $serviceRequest = ServiceRequest::findOrFail($id);

            // Authorization (Policy)
            $this->authorize('start', $serviceRequest);

            // Business logic
            $this->service->startWork($id, Auth::id());

            return back()->with('success', 'Work started successfully');

        } catch (AuthorizationException $e) {

            return back()->with('error', 'Unauthorized action');

        } catch (ModelNotFoundException $e) {

            return back()->with('error', 'Service request not found');

        } catch (\Exception $e) {

            return back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Complete work (in_progress → completed)
     */
    public function completeWork($id)
    {
        try {
            // Fetch request
            $serviceRequest = ServiceRequest::findOrFail($id);

            // Authorization (Policy)
            $this->authorize('complete', $serviceRequest);

            // Business logic
            $this->service->completeWork($id, Auth::id());

            return back()->with('success', 'Work completed successfully');

        } catch (AuthorizationException $e) {

            return back()->with('error', 'Unauthorized action');

        } catch (ModelNotFoundException $e) {

            return back()->with('error', 'Service request not found');

        } catch (\Exception $e) {

            return back()->with('error', 'Something went wrong');
        }
    }
}