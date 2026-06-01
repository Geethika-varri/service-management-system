<x-app-layout>

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            My Service Requests
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto mt-6">

        <div class="mb-4">
            <a href="{{ route('customer.requests.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded">
                + Create Request
            </a>
        </div>

        @if($requests->isEmpty())
            <p>No requests created yet.</p>
        @else
            @foreach($requests as $request)
                <div class="border p-3 mb-2 rounded">
                    <strong>{{ $request->title }}</strong><br>
                    Status: {{ $request->status }}
                </div>
            @endforeach
        @endif

    </div>

</x-app-layout>