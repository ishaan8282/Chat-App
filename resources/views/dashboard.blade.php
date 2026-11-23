<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Welcome to Real-time Chat App!</h3>
                <p class="text-gray-600 text-sm mb-4">Milestone 1: 1-to-1 Messages with Short Polling.</p>

                @if (isset($chatUser) && $chatUser)
                    <a href="{{ route('chat.show', $chatUser) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                        💬 Start Chatting with {{ $chatUser->name }} &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
