<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="flex h-screen">

        {{-- SIDEBAR --}}
        <aside class="w-64 sidebar flex flex-col">

            {{-- HEADER --}}
            <div class="p-4 text-lg font-bold border-b" style="border-color: var(--sea-dark);">
                Admin Panel
            </div>

            {{-- NAVIGATION --}}
            <nav class="flex-1 p-3 space-y-2 text-sm">

                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>

                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    Users
                </a>

                {{-- Disabled links (future modules) --}}
                <span class="block opacity-50 cursor-not-allowed">
                    Technicians
                </span>

                <a href="{{ route('admin.requests') }}"
                    class="{{ request()->routeIs('admin.requests') ? 'active' : '' }}">
                    Requests
                </a>

                <span class="block opacity-50 cursor-not-allowed">
                    SLA Reports
                </span>

            </nav>

            {{-- FOOTER --}}
            <div class="p-3 border-t text-xs" style="border-color: var(--sea-dark);">
                Logged in as {{ auth()->user()->role }}
            </div>

        </aside>

        {{-- MAIN --}}
        <div class="flex-1 flex flex-col">

            {{-- TOP BAR --}}
            <header class="bg-white shadow px-6 py-3 flex justify-between items-center">

                <h1 class="text-lg font-semibold text-gray-800">
                    @yield('title', 'Admin Panel')
                </h1>

                <div class="flex items-center gap-4 text-sm text-gray-600">

                    <span>{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">
                            Logout
                        </button>
                    </form>

                </div>

            </header>

            {{-- CONTENT --}}
            <main class="p-6 overflow-y-auto">

                <!-- SUCCESS MESSAGE -->
                @if(session('success'))
                    <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- ERROR MESSAGE -->
                @if($errors->any())
                    <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700">
                        <strong>Error:</strong>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

            </main>

        </div>

    </div>

</body>

</html>