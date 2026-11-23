<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 text-base font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                    {{ strtoupper(substr($recipient->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        {{ $recipient->name }}
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">
                        {{ $recipient->email }} &bull; <span class="text-emerald-700 font-semibold">Active Chat</span>
                    </p>
                </div>
            </div>

            <!-- Polling Indicator Badge -->
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-full shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Short Polling (2s) &bull; Requests: <strong id="polling-count" class="text-gray-900 font-mono">0</strong>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <!-- User Contacts Sidebar -->
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-col h-[650px]">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-500 uppercase">Contacts ({{ count($users) }})</h3>
                    </div>
                    <div class="space-y-1 overflow-y-auto flex-1 pr-1">
                        @foreach ($users as $user)
                            <a href="{{ route('chat.show', $user) }}"
                               class="flex items-center gap-3 p-2.5 rounded-lg transition {{ $user->id === $recipient->id ? 'bg-emerald-50 border border-emerald-300 text-gray-900 font-semibold' : 'hover:bg-gray-100 text-gray-700' }}">
                                <div class="flex items-center justify-center w-9 h-9 text-sm font-bold rounded-full bg-gray-200 text-gray-800">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="truncate">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Chat Messages & Input Panel -->
                <div class="md:col-span-3 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-col h-[650px] overflow-hidden">
                    <!-- Chat Header Bar -->
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Conversation with</span>
                            <span class="text-sm font-bold text-gray-900">{{ $recipient->name }}</span>
                        </div>
                        <span class="text-xs text-gray-500">User ID: #{{ $recipient->id }}</span>
                    </div>

                    <!-- Messages Scroll Container (WhatsApp style soft background) -->
                    <div id="messages-container" class="flex-1 p-5 space-y-3 overflow-y-auto bg-slate-100">
                        <div class="py-6 text-xs text-center text-gray-500" id="empty-state">
                            Messages load ho rahe hain...
                        </div>
                    </div>

                    <!-- Chat Input Form -->
                    <form id="chat-form" class="flex items-center gap-3 p-4 bg-white border-t border-gray-200">
                        <input type="text"
                               id="message-input"
                               name="message"
                               placeholder="Type your message here... (e.g. Hi Bruno!)"
                               required
                               autocomplete="off"
                               class="flex-1 px-4 py-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg shadow-inner focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <button type="submit"
                                id="send-btn"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white transition bg-emerald-600 rounded-lg shadow hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none">
                            <span>Send</span>
                            <svg class="w-4 h-4 ml-2 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Polling JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const messagesContainer = document.getElementById('messages-container');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const pollingCountEl = document.getElementById('polling-count');
            const emptyState = document.getElementById('empty-state');

            const currentUserId = {{ Auth::id() }};
            const fetchUrl = "{{ route('chat.messages', $recipient) }}";
            const sendUrl = "{{ route('chat.send', $recipient) }}";
            const csrfToken = "{{ csrf_token() }}";

            let requestCounter = 0;
            let lastMessageIds = new Set();

            function formatTime(isoString) {
                if (!isoString) return '';
                const date = new Date(isoString);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            function renderMessages(messages) {
                if (!messages || messages.length === 0) {
                    if (emptyState) {
                        emptyState.textContent = 'Abhi koi message nahi hai. Pehla message bhejiye!';
                    }
                    return;
                }

                if (emptyState) {
                    emptyState.remove();
                }

                let hasNewMessage = false;
                messages.forEach(msg => {
                    if (!lastMessageIds.has(msg.id)) {
                        hasNewMessage = true;
                        lastMessageIds.add(msg.id);

                        const isMine = msg.sender_id === currentUserId;
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;

                        // High contrast, clean styling:
                        // - Mine (Sent): Soft green (WhatsApp style) with crisp dark text
                        // - Other (Received): Crisp white bubble with border and dark text
                        const bubbleClasses = isMine
                            ? 'bg-emerald-100 text-gray-900 border border-emerald-300 rounded-2xl rounded-tr-none'
                            : 'bg-white text-gray-900 border border-gray-300 rounded-2xl rounded-tl-none shadow-sm';

                        messageDiv.innerHTML = `
                            <div class="max-w-[75%] px-4 py-2.5 ${bubbleClasses}">
                                <div class="text-sm text-gray-900 leading-relaxed font-normal break-words">${escapeHtml(msg.message)}</div>
                                <div class="text-[11px] mt-1 text-right text-gray-500 font-medium">
                                    ${formatTime(msg.created_at)}
                                </div>
                            </div>
                        `;
                        messagesContainer.appendChild(messageDiv);
                    }
                });

                if (hasNewMessage) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // 1. Fetch Messages via Short Polling
            async function fetchMessages() {
                requestCounter++;
                if (pollingCountEl) {
                    pollingCountEl.textContent = requestCounter;
                }

                try {
                    const response = await fetch(fetchUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (response.ok) {
                        const messages = await response.json();
                        renderMessages(messages);
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }

            // 2. Send Message via POST Request
            chatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const text = messageInput.value.trim();
                if (!text) return;

                messageInput.value = '';
                messageInput.focus();

                try {
                    const response = await fetch(sendUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ message: text }),
                    });

                    if (response.ok) {
                        await fetchMessages();
                    } else {
                        alert('Message bhejne mein error aaya.');
                    }
                } catch (err) {
                    console.error('Send error:', err);
                }
            });

            // Pehla fetch
            fetchMessages();

            // Har 2 second mein polling
            setInterval(fetchMessages, 2000);
        });
    </script>
</x-app-layout>
