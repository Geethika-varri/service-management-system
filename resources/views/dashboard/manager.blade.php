<x-app-layout>

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Manager Dashboard
        </h2>
    </x-slot>
    {{-- FILTERS --}}
    <div class="bg-white p-4 rounded-xl shadow border mb-6">

        <form method="GET">
            <div class="flex flex-wrap items-end gap-3">

                {{-- FROM --}}
                <div class="flex flex-col">
                    <label class="text-xs text-gray-500 mb-1">From</label>
                    <input type="date" name="from"
                        value="{{ $filters['from'] ?? '' }}"
                        class="border rounded-md px-2 py-1 text-sm w-36">
                </div>

                {{-- TO --}}
                <div class="flex flex-col">
                    <label class="text-xs text-gray-500 mb-1">To</label>
                    <input type="date" name="to"
                        value="{{ $filters['to'] ?? '' }}"
                        class="border rounded-md px-2 py-1 text-sm w-36">
                </div>

                {{-- TECHNICIAN --}}
                <div class="flex flex-col">
                    <label class="text-xs text-gray-500 mb-1">Technician</label>
                    <select name="technician_id"
                        class="border rounded-md px-2 py-1 text-sm w-40">
                        <option value="">All</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}"
                            {{ ($filters['technician_id'] ?? '') == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- STATUS --}}
                <div class="flex flex-col">
                    <label class="text-xs text-gray-500 mb-1">Status</label>
                    <select name="status"
                        class="border rounded-md px-2 py-1 text-sm w-36">
                        <option value="">All</option>
                        <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="assigned" {{ ($filters['status'] ?? '') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ ($filters['status'] ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex items-center gap-2">

                    {{-- APPLY --}}
                    <button
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-md">
                        Apply
                    </button>

                    {{-- RESET --}}
                    <a href="{{ url()->current() }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-1.5 rounded-md">
                        Reset
                    </a>

                </div>

            </div>
        </form>
    </div>

    <div class="max-w-5xl mx-auto mt-6 space-y-4">
        {{-- SLA ANALYTICS OVERVIEW --}}
        <div class="bg-white p-5 rounded-xl shadow border mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                SLA Analytics Overview
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-xl font-bold">{{ $totalRequests }}</p>
                </div>

                <div class="bg-green-50 p-3 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Completed</p>
                    <p class="text-xl font-bold text-green-600">{{ $completedRequests }}</p>
                </div>

                <div class="bg-yellow-50 p-3 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Delayed</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $delayedRequests }}</p>
                </div>

                <div class="bg-red-50 p-3 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Breaches</p>
                    <p class="text-xl font-bold text-red-600">{{ $totalBreaches }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Avg Resolve</p>
                    <p class="text-xl font-bold">
                        {{ $avgResolutionTime ? round($avgResolutionTime / 60, 1) . 'h' : '—' }}
                    </p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Avg Delay</p>
                    <p class="text-xl font-bold">
                        {{ $avgDelayTime ? round($avgDelayTime / 60, 1) . 'h' : '—' }}
                    </p>
                </div>

            </div>
        </div>

        @if($requests->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow border text-center text-gray-500">
            No service requests found.
        </div>
        @endif

        @foreach($requests as $req)
        <div class="p-4 rounded-lg shadow border transition
    {{ $req->isDelayed ? 'bg-red-50 border-red-400' : 'bg-white' }}">

            {{-- HEADER --}}
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-semibold text-lg text-gray-800 flex items-center">
                        {{ $req->title }}

                        @if($req->isDelayed)
                        <span class="ml-2 text-xs bg-red-600 text-white px-2 py-1 rounded">
                            SLA BREACH
                        </span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-600">
                        Customer: {{ $req->customer->name }}
                    </p>
                </div>

                <x-status-badge :status="$req->status" :type="$req->badge" />
            </div>

            {{-- TECHNICIAN --}}
            <div class="mt-4">
                <p class="text-sm">
                    Technician:
                    <strong class="{{ $req->technician ? 'text-green-600' : 'text-red-500' }}">
                        {{ $req->technician->name ?? 'Not Assigned' }}
                    </strong>
                </p>
            </div>

            {{-- ✅ ADD SLA BLOCK HERE --}}
            <div class="mt-4 border-t pt-3 text-sm space-y-1">

                <p>
                    Duration:
                    @if($req->duration !== null)
                    <strong>{{ $req->duration }} min</strong>
                    @else
                    —
                    @endif
                </p>

                <p>
                    Live:
                    @if($req->liveDuration !== null)
                    <strong>{{ $req->liveDuration }} min</strong>
                    @else
                    —
                    @endif
                </p>

                <p>
                    SLA:
                    @if($req->isDelayed)
                    <span class="text-red-600 font-semibold">Delayed</span>
                    @else
                    <span class="text-green-600">On Time</span>
                    @endif
                </p>

            </div>

            {{-- ACTIONS --}}
            <div class="mt-4 space-y-3">
                {{-- ASSIGN --}}
                @if(in_array('assign', $req->actions))
                <form method="POST" action="{{ route('manager.assign', $req->id) }}"
                    class="flex flex-col sm:flex-row sm:items-center gap-3">
                    @csrf

                    <select name="technician_id" required
                        class="w-full sm:w-60 border border-gray-300 rounded-md px-3 py-2 text-sm 
                                       bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">

                        <option value="">Select Technician</option>

                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">
                            {{ $tech->name }}
                        </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 
                                       text-white px-4 py-2 rounded-md text-sm font-medium shadow transition">
                        Assign
                    </button>
                </form>
                @endif

                {{-- REOPEN --}}
                @if(in_array('reopen', $req->actions))
                <form method="POST" action="{{ route('requests.reopen', $req->id) }}">
                    @csrf

                    <button type="submit"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium shadow">
                        Reopen
                    </button>
                </form>
                @endif

            </div>

        </div>
        @endforeach

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $requests->links() }}
        </div>

    </div>

</x-app-layout>