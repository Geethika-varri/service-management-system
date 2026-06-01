<x-app-layout>

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Technician Dashboard
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto mt-6 space-y-4">

        @if($requests->isEmpty())
            <div class="bg-white p-6 rounded-lg shadow border text-center text-gray-500">
                No assigned requests.
            </div>
        @endif

        @foreach($requests as $req)
            <div class="bg-white p-4 rounded-lg shadow border hover:shadow-md transition">

                {{-- HEADER --}}
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-lg text-gray-800">
                            {{ $req->title }}
                        </p>
                        <p class="text-sm text-gray-600">
                            Customer: {{ $req->customer->name }}
                        </p>
                    </div>

                    <span class="px-3 py-1 text-xs rounded-full
                        @if($req->status == 'assigned') bg-blue-100 text-blue-700
                        @elseif($req->status == 'in_progress') bg-yellow-100 text-yellow-700
                        @elseif($req->status == 'completed') bg-green-100 text-green-700
                        @endif
                    ">
                        {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                    </span>
                </div>

                {{-- ACTIONS --}}
                <div class="mt-4 flex gap-3">

                    {{-- START --}}
                    @if($req->status === 'assigned')
                        <form method="POST" action="{{ route('requests.start', $req->id) }}">
                            @csrf
                            <button type="submit"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm">
                                Start
                            </button>
                        </form>
                    @endif

                    {{-- COMPLETE --}}
                    @if($req->status === 'in_progress')
                        <form method="POST" action="{{ route('requests.complete', $req->id) }}">
                            @csrf
                            <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                Complete
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