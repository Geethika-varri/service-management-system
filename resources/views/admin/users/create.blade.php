@extends('layouts.admin')

@section('content')

<div class="p-6">

    <h2 class="text-xl font-semibold mb-4">Create User</h2>

    <div class="card p-4 max-w-lg">

        <form method="POST" action="{{ route('users.store') }}">
            @include('admin.users._form', [
                'buttonText' => 'Create User'
            ])
        </form>

    </div>

</div>

@endsection