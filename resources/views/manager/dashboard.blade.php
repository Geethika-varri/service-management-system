@php
    $technicians = $technicians ?? collect();
@endphp
<x-app-layout>
    <div class="p-6">

        <h2 class="text-2xl font-semibold mb-6">Manager Dashboard</h2>
        <form method="GET" class="mb-6 flex gap-4">

            <select name="status" class="border p-2 rounded">
                <option value="">All Status</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="assigned" {{ $status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <select name="sla" class="border p-2 rounded">
                <option value="">All SLA</option>
                <option value="on_time" {{ $sla === 'on_time' ? 'selected' : '' }}>On Time</option>
                <option value="nearing_breach" {{ $sla === 'nearing_breach' ? 'selected' : '' }}>Warning</option>
                <option value="breached" {{ $sla === 'breached' ? 'selected' : '' }}>Breached</option>
            </select>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Filter
            </button>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-white shadow rounded p-4">
                <h3 class="text-gray-500">Total Requests</h3>
                <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
            </div>

            <div class="bg-yellow-100 shadow rounded p-4">
                <h3 class="text-yellow-700">Pending</h3>
                <p class="text-2xl font-bold">{{ $stats['pending'] }}</p>
            </div>

            <div class="bg-blue-100 shadow rounded p-4">
                <h3 class="text-blue-700">Assigned</h3>
                <p class="text-2xl font-bold">{{ $stats['assigned'] }}</p>
            </div>

            <div class="bg-indigo-100 shadow rounded p-4">
                <h3 class="text-indigo-700">In Progress</h3>
                <p class="text-2xl font-bold">{{ $stats['in_progress'] }}</p>
            </div>

            <div class="bg-green-100 shadow rounded p-4">
                <h3 class="text-green-700">Completed</h3>
                <p class="text-2xl font-bold">{{ $stats['completed'] }}</p>
            </div>

            <div class="bg-red-100 shadow rounded p-4">
                <h3 class="text-red-700">SLA Breached</h3>
                <p class="text-2xl font-bold">{{ $stats['sla_breached'] }}</p>
            </div>

        </div>

        <div class="bg-white shadow rounded mt-6">
            <table class="w-full">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Technician</th>
                        <th class="p-3 text-left">SLA</th>
                        <th class="p-3 text-left">Action</th>

                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $req)
                        @php $slaStatus = $req->getDynamicSlaStatus(); @endphp

                        <tr class="
                                            @if($slaStatus === 'breached') bg-red-100
                                            @elseif($slaStatus === 'nearing_breach') bg-yellow-100
                                            @endif
                                        ">
                            <td class="p-3">{{ $req->id }}</td>
                            <td class="p-3">{{ $req->title }}</td>
                            <td class="p-3">
                                @if($req->status === 'pending')
                                    <span class="bg-gray-200 px-2 py-1 rounded">Pending</span>
                                @elseif($req->status === 'assigned')
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">Assigned</span>
                                @elseif($req->status === 'in_progress')
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">In Progress</span>
                                @elseif($req->status === 'completed')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded">Completed</span>
                                @endif
                            </td>

                            <td class="p-3">
                                @if($req->technician)
                                    <span class="text-blue-600 font-medium">
                                        {{ $req->technician->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Not Assigned</span>
                                @endif
                            </td>

                            <td class="p-3">{{ $slaStatus }}</td>

                            <td class="p-3">
                                @if($req->status === 'pending' && !$req->assigned_to)
                                    <form method="POST" action="{{ route('manager.assign', $req->id) }}">
                                        @csrf

                                        <select name="technician_id" class="border p-1 rounded" required>
                                            <option value="">Select</option>
                                            @foreach($technicians as $tech)
                                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                            @endforeach
                                        </select>

                                        <button onclick="this.disabled=true; this.form.submit();"
                                            class="bg-blue-500 text-white px-2 py-1 rounded ml-2">
                                            Assign
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">
                                No requests match your filters
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>
</x-app-layout>