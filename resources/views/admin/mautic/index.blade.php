@extends('layouts.admin')

@section('title', 'Mautic Marketing Automation')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mautic Marketing Automation</h1>
            <p class="text-gray-500 mt-1">Manage your marketing automation settings and sync contacts</p>
        </div>
        <div class="flex gap-3">
            <button onclick="testConnection()" class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Test Connection
            </button>
            <a href="{{ config('mautic.base_url') }}" target="_blank" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Open Mautic
            </a>
        </div>
    </div>

    {{-- Connection Status --}}
    <div id="connection-status" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3">
            <div id="status-icon" class="w-10 h-10 rounded-full flex items-center justify-center"></div>
            <div>
                <h3 id="status-title" class="font-semibold text-gray-900"></h3>
                <p id="status-message" class="text-gray-500 text-sm"></p>
            </div>
        </div>
    </div>

    {{-- Configuration Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Connection Settings --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Connection Settings
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mautic URL</label>
                    <input type="text" value="{{ config('mautic.base_url') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">API Username</label>
                    <input type="text" value="{{ config('mautic.api.username') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">API Password</label>
                    <input type="password" value="{{ config('mautic.api.password') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
            </div>
        </div>

        {{-- Tracking Settings --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tracking Settings
            </h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Visitor Tracking</p>
                        <p class="text-sm text-gray-500">Track page views and visitor behavior</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ config('mautic.tracking.enabled') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ config('mautic.tracking.enabled') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Contact Sync</p>
                        <p class="text-sm text-gray-500">Auto-sync users to Mautic contacts</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ config('mautic.sync.enabled') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ config('mautic.sync.enabled') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Pixel ID</label>
                    <input type="text" value="{{ config('mautic.tracking.pixel_id') ?: 'Not configured' }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
            </div>
        </div>

        {{-- Webhook URL --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Webhook Configuration
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
                    <div class="flex gap-2">
                        <input type="text" value="{{ url('/mautic/webhook') }}" readonly class="flex-1 border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                        <button onclick="copyToClipboard('{{ url('/mautic/webhook') }}')" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                    <input type="password" value="{{ config('mautic.webhook.secret') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <p class="text-sm text-gray-500">Configure this webhook URL in Mautic → Settings → Webhooks</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Quick Actions
            </h2>
            <div class="space-y-3">
                <button onclick="syncContacts()" class="w-full flex items-center gap-3 px-4 py-3 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync All Contacts
                </button>
                <a href="{{ route('admin.mautic.contacts') }}" class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    View Contacts
                </a>
                <button onclick="openMautic()" class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Open Mautic Dashboard
                </button>
            </div>
        </div>
    </div>

    {{-- Integration Guide --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Integration Guide</h2>
        <div class="prose prose-sm max-w-none text-gray-600">
            <h3 class="text-base font-semibold text-gray-900 mt-4">Automated Tracking</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>Visitor page views are tracked automatically via the tracking pixel</li>
                <li>User registrations are synced to Mautic contacts</li>
                <li>Completed orders are tracked with 10 points and "customer" tag</li>
                <li>Cart abandonment events are tracked for remarketing</li>
            </ul>

            <h3 class="text-base font-semibold text-gray-900 mt-4">Setup Steps</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li>Start Mautic: <code class="bg-gray-100 px-1 rounded">docker compose -f mautic/docker-compose.mautic.yml up -d</code></li>
                <li>Complete Mautic installation at <code class="bg-gray-100 px-1 rounded">http://localhost:8090</code></li>
                <li>Generate API credentials in Mautic → Settings → API Credentials</li>
                <li>Update .env with your Mautic credentials</li>
                <li>Configure webhook in Mautic → Settings → Webhooks</li>
            </ol>

            <h3 class="text-base font-semibold text-gray-900 mt-4">Point System</h3>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Page View:</strong> +1 point</li>
                <li><strong>Product View:</strong> +2 points</li>
                <li><strong>Order Completed:</strong> +10 points</li>
            </ul>
        </div>
    </div>
</div>

<script>
async function testConnection() {
    const statusDiv = document.getElementById('connection-status');
    const statusIcon = document.getElementById('status-icon');
    const statusTitle = document.getElementById('status-title');
    const statusMessage = document.getElementById('status-message');

    statusDiv.classList.remove('hidden');
    statusIcon.innerHTML = '<svg class="animate-spin w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    statusTitle.textContent = 'Testing Connection...';
    statusMessage.textContent = 'Please wait while we test the Mautic connection';

    try {
        const response = await fetch('{{ route("admin.mautic.test") }}');
        const data = await response.json();

        if (data.status === 'success') {
            statusIcon.innerHTML = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-green-100';
            statusTitle.textContent = 'Connection Successful';
            statusMessage.textContent = data.message;
        } else {
            statusIcon.innerHTML = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-red-100';
            statusTitle.textContent = 'Connection Failed';
            statusMessage.textContent = data.message;
        }
    } catch (error) {
        statusIcon.innerHTML = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-red-100';
        statusTitle.textContent = 'Connection Error';
        statusMessage.textContent = 'Failed to connect to Mautic. Please check your configuration.';
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Webhook URL copied to clipboard!');
    });
}

function openMautic() {
    window.open('{{ config("mautic.base_url") }}', '_blank');
}

async function syncContacts() {
    if (!confirm('Are you sure you want to sync all contacts to Mautic?')) {
        return;
    }

    try {
        const response = await fetch('{{ route("admin.mautic.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();

        if (data.success) {
            alert('Contacts synced successfully!');
        } else {
            alert('Failed to sync contacts: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error syncing contacts');
    }
}
</script>
@endsection
