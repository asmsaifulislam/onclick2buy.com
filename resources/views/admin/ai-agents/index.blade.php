@extends('layouts.admin')

@section('title', 'AI Agents Configuration')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">AI Agents Configuration</h1>
            <p class="text-gray-500 mt-1">Manage Rasa, Botpress, and Microsoft Bot Framework</p>
        </div>
        <div class="flex gap-3">
            <button onclick="testConnection()" class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Test Connection
            </button>
        </div>
    </div>

    {{-- Status Messages --}}
    <div id="status-message" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3">
            <div id="status-icon" class="w-10 h-10 rounded-full flex items-center justify-center"></div>
            <div>
                <h3 id="status-title" class="font-semibold text-gray-900"></h3>
                <p id="status-text" class="text-gray-500 text-sm"></p>
            </div>
        </div>
    </div>

    {{-- Active Provider --}}
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Active Provider</h2>
                <p class="text-white/80 mt-1">Currently using: <span class="font-bold" id="active-provider">{{ config('ai-agents.provider') }}</span></p>
            </div>
            <select id="provider-select" onchange="changeProvider()" class="bg-white/20 text-white border border-white/30 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-white/50">
                <option value="rasa" {{ config('ai-agents.provider') === 'rasa' ? 'selected' : '' }}>Rasa</option>
                <option value="botpress" {{ config('ai-agents.provider') === 'botpress' ? 'selected' : '' }}>Botpress</option>
                <option value="botframework" {{ config('ai-agents.provider') === 'botframework' ? 'selected' : '' }}>Microsoft Bot Framework</option>
            </select>
        </div>
    </div>

    {{-- Provider Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Rasa --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 {{ config('ai-agents.provider') === 'rasa' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Rasa</h3>
                    <p class="text-sm text-gray-500">Open Source Conversational AI</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">URL</span>
                    <span class="text-gray-900">{{ config('ai-agents.rasa.url') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Action Server</span>
                    <span class="text-gray-900">{{ config('ai-agents.rasa.action_url') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Status</span>
                    <span id="rasa-status" class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <button onclick="trainRasa()" class="w-full px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors text-sm font-medium">
                    Train Model
                </button>
                <a href="http://localhost:5005" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                    Open Rasa UI
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        {{-- Botpress --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 {{ config('ai-agents.provider') === 'botpress' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Botpress</h3>
                    <p class="text-sm text-gray-500">Visual Chatbot Builder</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">URL</span>
                    <span class="text-gray-900">{{ config('ai-agents.botpress.url') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Bot ID</span>
                    <span class="text-gray-900">{{ config('ai-agents.botpress.bot_id') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Status</span>
                    <span id="botpress-status" class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>
                </div>
            </div>
            <div class="mt-4">
                <a href="http://localhost:3100" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                    Open Botpress Studio
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        {{-- Microsoft Bot Framework --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 {{ config('ai-agents.provider') === 'botframework' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Bot Framework</h3>
                    <p class="text-sm text-gray-500">Microsoft AI Platform</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">App ID</span>
                    <span class="text-gray-900">{{ config('ai-agents.botframework.app_id') ?: 'Not configured' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Channel</span>
                    <span class="text-gray-900">{{ config('ai-agents.botframework.channel_id') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Status</span>
                    <span id="botframework-status" class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>
                </div>
            </div>
            <div class="mt-4">
                <a href="https://dev.botframework.com" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium">
                    Bot Framework Portal
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Setup Guide --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Setup Guide</h2>
        <div class="prose prose-sm max-w-none text-gray-600">
            <h3 class="text-base font-semibold text-gray-900 mt-4">Quick Start</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900">Rasa</h4>
                    <code class="text-xs bg-gray-200 px-1 rounded">docker compose -f ai-agents/docker-compose.rasa.yml up -d</code>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900">Botpress</h4>
                    <code class="text-xs bg-gray-200 px-1 rounded">docker compose -f ai-agents/docker-compose.botpress.yml up -d</code>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900">Bot Framework</h4>
                    <code class="text-xs bg-gray-200 px-1 rounded">Configure in .env file</code>
                </div>
            </div>

            <h3 class="text-base font-semibold text-gray-900 mt-4">Features</h3>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Rasa:</strong> Full control, custom NLU, on-premise</li>
                <li><strong>Botpress:</strong> Visual builder, quick setup, managed</li>
                <li><strong>Bot Framework:</strong> Microsoft ecosystem, multi-channel</li>
            </ul>

            <h3 class="text-base font-semibold text-gray-900 mt-4">Handoff to Human</h3>
            <p>The AI agent will automatically transfer to a human agent when users say: human, agent, person, support, help</p>
        </div>
    </div>
</div>

<script>
async function testConnection() {
    showStatus('syncing', 'Testing Connection...', 'Please wait while we test the AI agent connection');

    try {
        const response = await fetch('{{ route("admin.ai-agents.test") }}');
        const data = await response.json();

        if (data.status === 'success') {
            showStatus('success', 'Connection Successful', data.message);
            updateProviderStatus(data.provider, 'online');
        } else {
            showStatus('error', 'Connection Failed', data.message);
            updateProviderStatus(config('ai-agents.provider'), 'offline');
        }
    } catch (error) {
        showStatus('error', 'Connection Error', 'Failed to connect to AI agent');
    }
}

async function changeProvider() {
    const provider = document.getElementById('provider-select').value;

    try {
        const response = await fetch('{{ route("admin.ai-agents.provider.set") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ provider }),
        });
        const data = await response.json();

        if (data.success) {
            document.getElementById('active-provider').textContent = provider;
            showStatus('success', 'Provider Updated', `Active provider set to ${provider}`);
        } else {
            showStatus('error', 'Update Failed', data.error || 'Failed to change provider');
        }
    } catch (error) {
        showStatus('error', 'Update Error', 'Failed to change provider');
    }
}

async function trainRasa() {
    showStatus('syncing', 'Training Model...', 'This may take a few minutes');

    try {
        const response = await fetch('{{ route("admin.ai-agents.train") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();

        if (data.success) {
            showStatus('success', 'Training Started', data.message);
        } else {
            showStatus('error', 'Training Failed', data.message);
        }
    } catch (error) {
        showStatus('error', 'Training Error', 'Failed to start model training');
    }
}

function showStatus(status, title, message) {
    const statusDiv = document.getElementById('status-message');
    const statusIcon = document.getElementById('status-icon');
    const statusTitle = document.getElementById('status-title');
    const statusText = document.getElementById('status-text');

    statusDiv.classList.remove('hidden');

    if (status === 'syncing') {
        statusIcon.innerHTML = '<svg class="animate-spin w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-indigo-100';
    } else if (status === 'success') {
        statusIcon.innerHTML = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-green-100';
    } else {
        statusIcon.innerHTML = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-red-100';
    }

    statusTitle.textContent = title;
    statusText.textContent = message;
}

function updateProviderStatus(provider, status) {
    const statusEl = document.getElementById(`${provider}-status`);
    if (statusEl) {
        if (status === 'online') {
            statusEl.className = 'px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
            statusEl.textContent = 'Online';
        } else {
            statusEl.className = 'px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
            statusEl.textContent = 'Offline';
        }
    }
}
</script>
@endsection
