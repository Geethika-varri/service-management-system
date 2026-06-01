<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">
                My Service Requests
            </h2>

            <a href="{{ route('customer.requests.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow">
                + Create Request
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto mt-6 space-y-4">

        @if($requests->isEmpty())
            <div class="bg-white p-6 rounded-lg shadow border text-center text-gray-500">
                No requests created yet.
            </div>
        @else

            @foreach($requests as $req)
                <div class="bg-white p-4 rounded-lg shadow border hover:shadow-md transition">

                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ $req->title }}
                        </h3>

                        <span class="px-3 py-1 text-xs rounded-full
                            @if($req->status == 'pending') bg-gray-200 text-gray-700
                            @elseif($req->status == 'assigned') bg-blue-100 text-blue-700
                            @elseif($req->status == 'in_progress') bg-yellow-100 text-yellow-700
                            @elseif($req->status == 'completed') bg-green-100 text-green-700
                            @elseif($req->status == 'reopened') bg-orange-100 text-orange-700
                            @endif
                        ">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Technician:</strong>
                        {{ $req->technician->name ?? 'Not Assigned' }}
                    </p>

                </div>
            @endforeach

        @endif

    </div>

</x-app-layout>