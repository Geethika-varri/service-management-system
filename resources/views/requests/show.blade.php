@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-body">

            {{-- TITLE --}}
            <h4 class="mb-3">{{ $request->title }}</h4>

            {{-- STATUS --}}
            <div class="mb-3">
                <strong>Status:</strong>
                <x-status-badge :status="$request->status" :type="$badge" />
            </div>

            {{-- DESCRIPTION --}}
            <div class="mb-3">
                <strong>Description:</strong>
                <p class="mb-0">{{ $request->description }}</p>
            </div>

            {{-- TECHNICIAN --}}
            <div class="mb-3">
                <strong>Technician:</strong>
                {{ $request->technician->name ?? 'Not Assigned' }}
            </div>

            {{-- PROGRESS --}}
            <div class="mb-4">
                <x-progress-steps :progress="$progress" />
            </div>

            <div class="bg-white p-4 rounded shadow mt-4">
                <h3 class="font-semibold mb-3">SLA Details</h3>

                <div class="space-y-2 text-sm">

                    <div>
                        <strong>Started At:</strong>
                        {{ $request->started_at ?? 'Not Started' }}
                    </div>

                    <div>
                        <strong>Completed At:</strong>
                        {{ $request->completed_at ?? 'Not Completed' }}
                    </div>

                    <div>
                        <strong>Duration:</strong>
                        @if($duration !== null)
                        {{ $duration }} minutes
                        @else
                        Not Available
                        @endif
                    </div>

                    <div>
                        <strong>Live Duration:</strong>
                        @if($liveDuration !== null)
                        {{ $liveDuration }} minutes
                        @else
                        —
                        @endif
                    </div>

                    <div>
                        <strong>Status:</strong>
                        @if($isDelayed)
                        <span class="text-red-600 font-bold">Delayed</span>
                        @else
                        <span class="text-green-600">On Time</span>
                        @endif
                    </div>

                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex gap-2">

                @if(in_array('start', $actions))
                <form method="POST" action="{{ route('requests.start', $request->id) }}">
                    @csrf
                    <button class="btn btn-primary">Start Work</button>
                </form>
                @endif

                @if(in_array('complete', $actions))
                <form method="POST" action="{{ route('requests.complete', $request->id) }}">
                    @csrf
                    <button class="btn btn-success">Complete</button>
                </form>
                @endif

                @if(in_array('reopen', $actions))
                <form method="POST" action="{{ route('requests.reopen', $request->id) }}">
                    @csrf
                    <button class="btn btn-warning">Reopen</button>
                </form>
                @endif

            </div>

        </div>
    </div>

</div>

@endsection