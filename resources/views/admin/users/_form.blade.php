@csrf

<div class="space-y-4">

    <!-- NAME -->
    <div>
        <label class="block mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
            class="w-full border rounded px-3 py-2">

        @error('name')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    <!-- EMAIL -->
    <div>
        <label class="block mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
            class="w-full border rounded px-3 py-2">

        @error('email')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    <!-- PASSWORD -->
    <div>
        <label class="block mb-1">Password</label>
        <input type="password" name="password" class="w-full border rounded px-3 py-2">

        @error('password')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror

        @if(isset($user))
            <small class="text-gray-500">Leave blank to keep current password</small>
        @endif
    </div>

    <!-- ROLE -->
    <div>
        <label class="block mb-1">Role</label>

        @if(isset($user) && $user->role === 'admin')
            <!-- LOCKED ROLE -->
            <input type="text" value="Admin" class="w-full border rounded px-3 py-2 bg-gray-100" disabled>

            <!-- Hidden input to preserve value -->
            <input type="hidden" name="role" value="admin">
        @else

            @php
                $roles = ['manager', 'technician', 'customer'];
            @endphp

            <select name="role" class="w-full border rounded px-3 py-2">

                <!-- DEFAULT EMPTY OPTION -->
                <option value="">Select Role</option>

                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ old('role', $user->role ?? '') === $role ? 'selected' : '' }}>
                        {{ ucfirst($role) }}
                    </option>
                @endforeach

            </select>

        @endif

        @error('role')
            <div class="text-red-500 text-sm">{{ $message }}</div>
        @enderror
    </div>

    <!-- SUBMIT -->
    <div>
        <button class="btn-primary">
            {{ $buttonText }}
        </button>
    </div>

</div>