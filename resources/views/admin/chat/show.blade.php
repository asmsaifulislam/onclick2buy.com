@extends('layouts.admin')

@section('title', 'Chat with ' . $session->display_name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('admin.chat.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-gray-900">{{ $session->display_name }}</h1>
            <p class="text-xs text-gray-500">
                @if($session->visitor_email){{ $session->visitor_email }} &middot; @endif
                {{ $session->user_id ? 'Registered user' : 'Guest visitor' }} &middot;
                <span class="{{ $session->status === 'open' ? 'text-green-600' : 'text-gray-400' }}">{{ ucfirst($session->status) }}</span>
            </p>
        </div>
        @if($session->status === 'open')
            <form method="POST" action="{{ route('admin.chat.close', $session) }}">
                @csrf
                <button type="submit" class="text-sm px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors font-medium">Close Chat</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.chat.reopen', $session) }}">
                @csrf
                <button type="submit" class="text-sm px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors font-medium">Reopen</button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col" style="height: 520px;">
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
            @foreach($session->messages->sortBy('created_at') as $msg)
                <div class="flex {{ $msg->is_admin ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%]">
                        <div class="px-4 py-2.5 rounded-2xl {{ $msg->is_admin ? 'bg-indigo-600 text-white rounded-br-md' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-md shadow-sm' }}">
                            <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 {{ $msg->is_admin ? 'text-right' : '' }}">
                            {{ $msg->user ? $msg->user->name : ($msg->is_admin ? 'Support' : $session->display_name) }}
                            &middot; {{ $msg->created_at->format('h:i A') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        @if($session->status === 'open')
            <div class="border-t border-gray-200 p-4 bg-white">
                <form id="chat-form" class="flex items-center gap-3">
                    @csrf
                    <input type="text" id="chat-input" placeholder="Type your message..." class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" autocomplete="off">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-2.5 text-sm font-medium transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Send
                    </button>
                </form>
            </div>
        @else
            <div class="border-t border-gray-200 p-4 bg-gray-50 text-center text-sm text-gray-400">
                This chat session has been closed.
            </div>
        @endif
    </div>
</div>

<script>
(function() {
    const messages = document.getElementById('chat-messages');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const sessionId = {{ $session->id }};
    let lastId = {{ $session->messages->max('id') ?? 0 }};

    messages.scrollTop = messages.scrollHeight;

    function addMessage(msg) {
        const div = document.createElement('div');
        div.className = 'flex ' + (msg.is_admin ? 'justify-end' : 'justify-start');
        const bgClass = msg.is_admin ? 'bg-indigo-600 text-white rounded-br-md' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-md shadow-sm';
        div.innerHTML = `
            <div class="max-w-[75%]">
                <div class="px-4 py-2.5 rounded-2xl ${bgClass}">
                    <p class="text-sm leading-relaxed">${msg.message}</p>
                </div>
                <p class="text-[10px] text-gray-400 mt-1 ${msg.is_admin ? 'text-right' : ''}">${msg.sender} &middot; ${msg.created_at}</p>
            </div>
        `;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            input.value = '';

            try {
                const res = await fetch('/admin/chat/{{ $session->id }}/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                });
                const data = await res.json();
                if (data.id) {
                    lastId = data.id;
                    addMessage(data);
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    setInterval(async () => {
        try {
            const res = await fetch('/admin/chat/{{ $session->id }}/poll?last_id=' + lastId, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '' },
            });
            const data = await res.json();
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    if (msg.id > lastId) {
                        lastId = msg.id;
                        addMessage(msg);
                    }
                });
            }
        } catch (err) {}
    }, 5000);
})();
</script>
@endsection
