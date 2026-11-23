<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Chat with {{ $recipient->name }}
                </h2>
                <p class="text-xs text-amber-600 font-medium mt-1">
                    ⚡ Real-time Mode: <span class="font-bold underline">Short Polling (fetch every 2s)</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full font-mono">
                    Requests: <span id="polling-count">0</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- User Contacts Sidebar -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <h3 class="font-bold text-gray-700 text-sm mb-3 uppercase tracking-wider">Contacts</h3>
                    <div class="space-y-2">
                        @foreach ($users as $user)
                            <a href="{{ route('chat.show', $user) }}"
                               class="flex items-center gap-3 p-3 rounded-lg transition {{ $user->id === $recipient->id ? 'bg-indigo-50 border border-indigo-200 text-indigo-700 font-semibold' : 'hover:bg-gray-50 text-gray-700' }}">
                                <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="truncate">
                                    <div class="text-sm font-medium">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $user->email }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Chat Messages & Input Panel -->
                <div class="md:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col h-[650px]">
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">
                                {{ strtoupper(substr($recipient->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">{{ $recipient->name }}</h4>
                                <span class="text-xs text-gray-500">ID: {{ $recipient->id }} ({{ $recipient->email }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Scroll Container -->
                    <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50">
                        <div class="text-center text-xs text-gray-400 py-4" id="empty-state">
                            Messages load ho rahe hain...
                        </div>
                    </div>

                    <!-- Chat Input Form -->
                    <form id="chat-form" class="p-4 border-t border-gray-100 bg-white flex items-center gap-3">
                        <input type="text"
                               id="message-input"
                               name="message"
                               placeholder="Type your message here... (e.g. Hello Bruno!)"
                               required
                               autocomplete="off"
                               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3">
                        <button type="submit"
                                id="send-btn"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                            <span>Send</span>
                            <svg class="ml-2 w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                // Check agar koi naya message aaya hai
                let hasNewMessage = false;
                messages.forEach(msg => {
                    if (!lastMessageIds.has(msg.id)) {
                        hasNewMessage = true;
                        lastMessageIds.add(msg.id);

                        const isMine = msg.sender_id === currentUserId;
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;

                        messageDiv.innerHTML = `
                            <div class="max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm ${
                                isMine 
                                    ? 'bg-indigo-600 text-white rounded-br-none' 
                                    : 'bg-white text-gray-800 border border-gray-200 rounded-bl-none'
                            }">
                                <div class="break-words leading-relaxed">${escapeHtml(msg.message)}</div>
                                <div class="text-[10px] mt-1 text-right ${isMine ? 'text-indigo-200' : 'text-gray-400'}">
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
                pollingCountEl.textContent = requestCounter;

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
                        // Immediately poll taaki bheja hua message turant screen par aa jaye
                        await fetchMessages();
                    } else {
                        alert('Message bhejne mein error aaya.');
                    }
                } catch (err) {
                    console.error('Send error:', err);
                }
            });

            // Pehli baar turant fetch karo
            fetchMessages();

            // HAR 2 SECOND MEIN POLL KARO (The Heart of Milestone 1)
            setInterval(fetchMessages, 2000);
        });
    </script>
</x-app-layout>
