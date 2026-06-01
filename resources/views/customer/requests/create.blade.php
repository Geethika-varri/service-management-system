<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Service Request
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-sm border">

        {{-- SUCCESS --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- ERRORS --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.requests.store') }}">
            @csrf

            <div class="space-y-5">

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title
                    </label>
                    <input type="text" name="title"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2"
                        required>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea name="description"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2"
                        required></textarea>
                </div>
                <div class="mt-4">
                    <label class="block mb-1">Priority</label>

                    <select name="priority" class="w-full border rounded p-2">
                        <option value="">Select Priority</option>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>

                    @error('priority')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            {{-- Actions --}}
            <div class="mt-6 flex justify-between items-center">

                <a href="/customer/dashboard" class="text-gray-600 hover:text-gray-800 text-sm">
                    ← Back
                </a>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-medium shadow">
                    Submit Request
                </button>

            </div>

        </form>
    </div>

</x-app-layout>