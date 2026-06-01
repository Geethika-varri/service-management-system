<x-app-layout>
    <div class="p-6">

        <h2 class="text-2xl font-semibold mb-6">Technician Dashboard</h2>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full">

                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="p-4 text-left">ID</th>
                        <th class="p-4 text-left">Title</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">SLA</th>
                        <th class="p-4 text-left">Remaining</th>
                        <th class="p-4 text-left">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($requests as $request)
                        @php $sla = $request->getDynamicSlaStatus(); @endphp

                        <tr class="
                            hover:bg-gray-50 transition
                            @if($sla === 'breached') bg-red-100
                            @elseif($sla === 'nearing_breach') bg-yellow-100
                            @endif
                        ">

                            <td class="p-4 font-medium">{{ $request->id }}</td>

                            <td class="p-4">
                                {{ $request->title ?? 'N/A' }}
                            </td>

                            <td class="p-4">
                                @if($request->status === 'assigned')
                                    <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded-full">
                                        Assigned
                                    </span>
                                @elseif($request->status === 'in_progress')
                                    <span class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full">
                                        In Progress
                                    </span>
                                @elseif($request->status === 'completed')
                                    <span class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full">
                                        Completed
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($sla === 'breached')
                                    <span class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-full">
                                        Breached
                                    </span>
                                @elseif($sla === 'nearing_breach')
                                    <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded-full">
                                        Warning
                                    </span>
                                @elseif($sla === 'on_time')
                                    <span class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full">
                                        On Time
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-4">
                                @if($request->started_at)
                                    {{ $request->getFormattedRemainingTime() }}
                                @else
                                    <span class="text-gray-400">Not Started</span>
                                @endif

                                @if($sla === 'breached')
                                    <span class="text-red-600 text-sm">(Overdue)</span>
                                @endif
                            </td>

                            <td class="p-4 flex gap-2">

                                {{-- assigned → in_progress --}}
                                @if($request->status === 'assigned')
                                    <form method="POST" action="{{ route('technician.start', $request->id) }}">
                                        @csrf
                                        <button 
                                        onclick="this.disabled=true; this.form.submit();" 
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded shadow">
                                        Start Work
                                    </button>
                                    </form>
                                @endif

                                {{-- in_progress → completed --}}
                                @if($request->status === 'in_progress')
                                    <form method="POST" action="{{ route('technician.complete', $request->id) }}">
                                        @csrf
                                        <button 
                                            onclick="this.disabled=true; this.form.submit();" 
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                                            Mark Completed
                                        </button>
                                    </form>
                                @endif

                                {{-- completed --}}
                                @if($request->status === 'completed')
                                    <span class="text-gray-500 font-medium">Done</span>
                                @endif

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">
                                No requests available
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>