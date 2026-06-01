@extends('layouts.admin')

@section('content')

<div class="p-6">

    <h2 class="text-xl font-semibold mb-4">Edit User</h2>

    <div class="card p-4 max-w-lg">

        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            @include('admin.users._form', [
            'user' => $user,
            'buttonText' => 'Update User'
            ])
        </form>

    </div>

</div>

@endsection