@extends('layouts.admin')

@section('content')

    <div class="p-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Service Requests</h2>
        </div>

        <!-- TABLE -->
        <div class="card overflow-x-auto">

            <table class="w-full text-left">

                <thead>
                    <tr class="border-b">
                        <th class="p-3">Title</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Technician</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">SLA</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($requests as $req)
                        <tr class="border-b hover:bg-gray-50">

                            <!-- TITLE -->
                            <td class="p-3">
                                {{ $req->title }}
                            </td>

                            <!-- CUSTOMER -->
                            <td class="p-3">
                                {{ $req->customer->name ?? '-' }}
                            </td>

                            <!-- TECHNICIAN -->
                            <td class="p-3">
                                {{ $req->technician->name ?? 'Unassigned' }}
                            </td>

                            <!-- STATUS -->
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-700">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>

                            <!-- SLA -->
                            <td class="p-3">
                                {{ method_exists($req, 'getSlaStatus') ? $req->getSlaStatus() : '-' }}
                            </td>

                            <td class="p-3 text-right space-y-2">

                                {{-- FORCE ASSIGN --}}
                                @if($req->status !== 'completed')
                                    <form method="POST" action="{{ route('admin.force.assign', $req->id) }}">
                                        @csrf

                                        <select name="technician_id" required class="border px-2 py-1 rounded">
                                            <option value="">Select Tech</option>
                                            @foreach($technicians as $tech)
                                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                            @endforeach
                                        </select>

                                        <button type="submit"
                                            onclick="this.disabled=true; this.form.submit();"
                                            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 disabled:opacity-50">
                                            Assign
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">Assigned</span>
                                @endif


                                {{-- FORCE COMPLETE --}}
                                @if($req->status !== 'completed')
                                    <form method="POST" action="{{ route('admin.force.complete', $req->id) }}">
                                        @csrf

                                        <button type="submit"
                                            onclick="this.disabled=true; this.form.submit();"
                                            class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 disabled:opacity-50">
                                            Complete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">Done</span>
                                @endif

                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                No service requests found
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection