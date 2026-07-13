@extends('layouts.admin')

@section('title', 'AI Chatbot Settings')

@section('content')
<div style="max-width:900px; margin:0 auto;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
        <div>
            <h1 style="font-size:24px; font-weight:700; color:#111827;">AI Chatbot Settings</h1>
            <p style="color:#6b7280; margin-top:4px;">Configure the frontend chatbot widget and AI provider</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="testConnection()" style="display:flex; align-items:center; gap:6px; padding:8px 16px; background:#fff; border:1px solid #d1d5db; border-radius:8px; cursor:pointer; font-size:14px; color:#374151;">
                <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Test Connection
            </button>
            <button onclick="testChat()" style="display:flex; align-items:center; gap:6px; padding:8px 16px; background:#2563eb; border:none; border-radius:8px; cursor:pointer; font-size:14px; color:#fff;">
                <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Test Chat
            </button>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#ecfdf5; border:1px solid #6ee7b7; color:#065f46; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px;">
        {{ session('success') }}
    </div>
    @endif

    <div id="test-result" style="display:none; margin-bottom:20px; padding:12px 16px; border-radius:8px; font-size:14px;"></div>

    <form method="POST" action="{{ route('admin.ai-chat-settings.update') }}">
        @csrf
        @method('PUT')

        {{-- Enable/Disable --}}
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; margin-bottom:20px;">
            <h2 style="font-size:16px; font-weight:600; color:#111827; margin-bottom:16px;">General</h2>
            <label style="display:flex; align-items:center; gap:12px; cursor:pointer;">
                <input type="hidden" name="enabled" value="0">
                <input type="checkbox" name="enabled" value="1" {{ ($settings['enabled'] ?? '1') == '1' ? 'checked' : '' }}
                    style="width:18px; height:18px; accent-color:#2563eb; cursor:pointer;">
                <div>
                    <span style="font-size:14px; font-weight:500; color:#111827;">Enable Chatbot Widget</span>
                    <p style="font-size:12px; color:#6b7280; margin-top:2px;">Show the chat bubble on the frontend</p>
                </div>
            </label>
        </div>

        {{-- Provider Selection --}}
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; margin-bottom:20px;">
            <h2 style="font-size:16px; font-weight:600; color:#111827; margin-bottom:16px;">AI Provider</h2>
            <p style="font-size:13px; color:#6b7280; margin-bottom:12px;">Choose which AI service powers the chatbot</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <label style="display:flex; align-items:center; gap:10px; padding:14px; border:2px solid {{ ($settings['provider'] ?? 'rasa') === 'external' ? '#2563eb' : '#e5e7eb' }}; border-radius:10px; cursor:pointer; background:{{ ($settings['provider'] ?? 'rasa') === 'external' ? '#eff6ff' : '#fff' }};">
                    <input type="radio" name="provider" value="external" {{ ($settings['provider'] ?? 'rasa') === 'external' ? 'checked' : '' }}
                        onchange="showProvider('external')" style="accent-color:#2563eb;">
                    <div>
                        <span style="font-size:14px; font-weight:600; color:#111827;">External API</span>
                        <p style="font-size:11px; color:#6b7280;">Kilo AI, OpenRouter, etc.</p>
                    </div>
                </label>
                <label style="display:flex; align-items:center; gap:10px; padding:14px; border:2px solid {{ ($settings['provider'] ?? 'rasa') === 'rasa' ? '#2563eb' : '#e5e7eb' }}; border-radius:10px; cursor:pointer; background:{{ ($settings['provider'] ?? 'rasa') === 'rasa' ? '#eff6ff' : '#fff' }};">
                    <input type="radio" name="provider" value="rasa" {{ ($settings['provider'] ?? 'rasa') === 'rasa' ? 'checked' : '' }}
                        onchange="showProvider('rasa')" style="accent-color:#2563eb;">
                    <div>
                        <span style="font-size:14px; font-weight:600; color:#111827;">Rasa</span>
                        <p style="font-size:11px; color:#6b7280;">Self-hosted (advanced)</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- External API Config --}}
        <div id="provider-external" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; margin-bottom:20px; {{ ($settings['provider'] ?? 'rasa') !== 'external' ? 'display:none;' : '' }}">
            <h2 style="font-size:16px; font-weight:600; color:#111827; margin-bottom:4px;">External API Configuration</h2>
            <p style="font-size:13px; color:#6b7280; margin-bottom:16px;">Works with any OpenAI-compatible API (Kilo AI, OpenRouter, Groq, Together AI, etc.)</p>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:500; color:#374151; margin-bottom:6px;">API Endpoint URL *</label>
                <input type="url" name="external_api_url" value="{{ $settings['external_api_url'] ?? '' }}"
                    placeholder="https://api.kilo.ai/api/gateway/chat/completions"
                    style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box;">
                <p style="font-size:11px; color:#9ca3af; margin-top:4px;">Full URL including /chat/completions (OpenAI-compatible format)</p>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:500; color:#374151; margin-bottom:6px;">API Key *</label>
                <input type="password" name="external_api_key" value="{{ $settings['external_api_key'] ?? '' }}"
                    placeholder="sk-xxxx or kilo_xxxxxxxx"
                    style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box;">
                <p style="font-size:11px; color:#9ca3af; margin-top:4px;">Leave blank to keep existing key</p>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:500; color:#374151; margin-bottom:6px;">Model Name</label>
                <input type="text" name="external_model" value="{{ $settings['external_model'] ?? 'bytedance-seed/dola-seed-2.0-pro:free' }}"
                    placeholder="kilo-auto/free"
                    style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box;">
                <p style="font-size:11px; color:#9ca3af; margin-top:4px;">Model ID from your API provider</p>
            </div>

            <div>
                <label style="display:block; font-size:13px; font-weight:500; color:#374151; margin-bottom:6px;">System Prompt</label>
                <textarea name="external_system_prompt" rows="4"
                    style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box; resize:vertical;">{{ $settings['external_system_prompt'] ?? '' }}</textarea>
                <p style="font-size:11px; color:#9ca3af; margin-top:4px;">Instructions for the AI about your store, products, and support policies</p>
            </div>
        </div>

        {{-- Handoff --}}
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; margin-bottom:20px;">
            <h2 style="font-size:16px; font-weight:600; color:#111827; margin-bottom:16px;">Human Handoff</h2>
            <label style="display:flex; align-items:center; gap:12px; cursor:pointer; margin-bottom:12px;">
                <input type="hidden" name="handoff_enabled" value="0">
                <input type="checkbox" name="handoff_enabled" value="1" {{ ($settings['handoff_enabled'] ?? '1') == '1' ? 'checked' : '' }}
                    style="width:18px; height:18px; accent-color:#2563eb; cursor:pointer;">
                <div>
                    <span style="font-size:14px; font-weight:500; color:#111827;">Enable Human Handoff</span>
                    <p style="font-size:12px; color:#6b7280; margin-top:2px;">Transfer to live agent when user types handoff keywords</p>
                </div>
            </label>
            <div>
                <label style="display:block; font-size:13px; font-weight:500; color:#374151; margin-bottom:6px;">Handoff Keywords (comma separated)</label>
                <input type="text" name="handoff_keywords" value="{{ $settings['handoff_keywords'] ?? 'human,agent,person,support,help' }}"
                    style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box;">
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="submit" style="padding:10px 24px; background:#2563eb; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer;">
                Save Settings
            </button>
        </div>
    </form>

    {{-- Test Chat Section --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; margin-top:24px;">
        <h2 style="font-size:16px; font-weight:600; color:#111827; margin-bottom:16px;">Test Chat</h2>
        <div id="test-chat-body" style="height:200px; overflow-y:auto; padding:12px; background:#f9fafb; border-radius:8px; margin-bottom:12px; font-size:14px;"></div>
        <div style="display:flex; gap:8px;">
            <input type="text" id="test-chat-input" placeholder="Type a test message..."
                onkeydown="if(event.key==='Enter') sendTestMsg()"
                style="flex:1; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px;">
            <button onclick="sendTestMsg()" style="padding:10px 20px; background:#2563eb; color:#fff; border:none; border-radius:8px; font-size:14px; cursor:pointer;">Send</button>
        </div>
    </div>
</div>

<script>
function showProvider(name) {
    document.getElementById('provider-external').style.display = name === 'external' ? 'block' : 'none';
}

async function testConnection() {
    const el = document.getElementById('test-result');
    el.style.display = 'block';
    el.style.background = '#f3f4f6';
    el.style.color = '#374151';
    el.textContent = 'Testing connection...';
    try {
        const res = await fetch('{{ route("admin.ai-chat-settings.test") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();
        el.style.background = data.status === 'success' ? '#ecfdf5' : '#fef2f2';
        el.style.color = data.status === 'success' ? '#065f46' : '#991b1b';
        el.style.border = '1px solid ' + (data.status === 'success' ? '#6ee7b7' : '#fca5a5');
        el.textContent = data.message || 'Unknown result';
    } catch(e) {
        el.style.background = '#fef2f2';
        el.style.color = '#991b1b';
        el.textContent = 'Error: ' + e.message;
    }
}

async function testChat() {
    const body = document.getElementById('test-chat-body');
    const input = document.getElementById('test-chat-input');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    body.innerHTML += '<div style="text-align:right; margin:8px 0;"><span style="background:#e0e7ff; padding:6px 12px; border-radius:10px; display:inline-block; max-width:80%;">' + escHtml(text) + '</span></div>';
    body.scrollTop = body.scrollHeight;
    try {
        const res = await fetch('{{ route("admin.ai-chat-settings.test-chat") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        body.innerHTML += '<div style="text-align:left; margin:8px 0;"><span style="background:#f3f4f6; padding:6px 12px; border-radius:10px; display:inline-block; max-width:80%;">' + escHtml(data.reply || 'No reply') + '</span></div>';
        body.scrollTop = body.scrollHeight;
    } catch(e) {
        body.innerHTML += '<div style="text-align:left; margin:8px 0;"><span style="background:#fef2f2; padding:6px 12px; border-radius:10px; display:inline-block;">Error: ' + escHtml(e.message) + '</span></div>';
    }
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
</script>
@endsection
