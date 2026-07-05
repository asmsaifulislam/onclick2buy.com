@extends('layouts.admin')

@section('title', 'ERPNext Integration')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ERPNext Integration</h1>
            <p class="text-gray-500 mt-1">Manage your ERP/Inventory system integration</p>
        </div>
        <div class="flex gap-3">
            <button onclick="testConnection()" class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Test Connection
            </button>
            <a href="{{ config('erpnext.base_url') }}" target="_blank" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Open ERPNext
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

    {{-- Sync Status --}}
    <div id="sync-status" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3">
            <div id="sync-icon" class="w-10 h-10 rounded-full flex items-center justify-center"></div>
            <div>
                <h3 id="sync-title" class="font-semibold text-gray-900"></h3>
                <p id="sync-message" class="text-gray-500 text-sm"></p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">ERPNext URL</label>
                    <input type="text" value="{{ config('erpnext.base_url') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                    <input type="text" value="{{ config('erpnext.site') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                    <input type="password" value="{{ config('erpnext.api.key') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                    <input type="password" value="{{ config('erpnext.api.secret') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
            </div>
        </div>

        {{-- Sync Settings --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Sync Settings
            </h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Auto Sync Products</p>
                        <p class="text-sm text-gray-500">Sync products to ERPNext items</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ config('erpnext.sync.auto_sync_products') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ config('erpnext.sync.auto_sync_products') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Auto Sync Orders</p>
                        <p class="text-sm text-gray-500">Sync orders to ERPNext sales orders</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ config('erpnext.sync.auto_sync_orders') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ config('erpnext.sync.auto_sync_orders') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Auto Sync Inventory</p>
                        <p class="text-sm text-gray-500">Sync inventory levels from ERPNext</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ config('erpnext.sync.auto_sync_inventory') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ config('erpnext.sync.auto_sync_inventory') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ERPNext Modules --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                ERPNext Configuration
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <input type="text" value="{{ config('erpnext.company') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                    <input type="text" value="{{ config('erpnext.warehouse') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Group</label>
                    <input type="text" value="{{ config('erpnext.item_group') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-600">
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Quick Actions
            </h2>
            <div class="space-y-3">
                <button onclick="syncProducts()" class="w-full flex items-center gap-3 px-4 py-3 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Sync Products to ERPNext
                </button>
                <button onclick="syncCustomers()" class="w-full flex items-center gap-3 px-4 py-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Sync Customers
                </button>
                <button onclick="syncOrders()" class="w-full flex items-center gap-3 px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Sync Orders
                </button>
                <button onclick="syncInventory()" class="w-full flex items-center gap-3 px-4 py-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Sync Inventory from ERPNext
                </button>
                <a href="{{ route('admin.erpnext.items') }}" class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    View ERPNext Items
                </a>
            </div>
        </div>
    </div>

    {{-- Setup Guide --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Setup Guide</h2>
        <div class="prose prose-sm max-w-none text-gray-600">
            <h3 class="text-base font-semibold text-gray-900 mt-4">Installation</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li>Start ERPNext: <code class="bg-gray-100 px-1 rounded">docker compose -f erpnext/docker-compose.erpnext.yml up -d</code></li>
                <li>Wait 2-3 minutes for initialization</li>
                <li>Open ERPNext: <code class="bg-gray-100 px-1 rounded">http://localhost:8000</code></li>
                <li>Complete setup wizard</li>
                <li>Generate API key: Settings → User → API Access</li>
            </ol>

            <h3 class="text-base font-semibold text-gray-900 mt-4">API Key Setup</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li>Login to ERPNext as Administrator</li>
                <li>Go to Settings → User → Administrator</li>
                <li>Click "API Access" tab</li>
                <li>Generate new API key</li>
                <li>Copy API Key and API Secret to .env</li>
            </ol>

            <h3 class="text-base font-semibold text-gray-900 mt-4">Data Sync</h3>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Products:</strong> Syncs product name, price, stock, category</li>
                <li><strong>Customers:</strong> Syncs user email, name, phone</li>
                <li><strong>Orders:</strong> Creates sales orders in ERPNext</li>
                <li><strong>Inventory:</strong> Syncs stock levels from ERPNext</li>
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
    statusMessage.textContent = 'Please wait while we test the ERPNext connection';

    try {
        const response = await fetch('{{ route("admin.erpnext.test") }}');
        const data = await response.json();

        if (data.status === 'success') {
            statusIcon.innerHTML = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-green-100';
            statusTitle.textContent = 'Connection Successful';
            statusMessage.textContent = data.message + (data.version ? ' (Version: ' + data.version + ')' : '');
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
        statusMessage.textContent = 'Failed to connect to ERPNext. Please check your configuration.';
    }
}

async function syncProducts() {
    showSyncStatus('syncing', 'Syncing Products...', 'Please wait while we sync products to ERPNext');

    try {
        const response = await fetch('{{ route("admin.erpnext.sync.products") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();

        if (data.success) {
            showSyncStatus('success', 'Products Synced', data.synced + ' products synced successfully' + (data.failed > 0 ? ', ' + data.failed + ' failed' : ''));
        } else {
            showSyncStatus('error', 'Sync Failed', data.error || 'Failed to sync products');
        }
    } catch (error) {
        showSyncStatus('error', 'Sync Error', 'An error occurred while syncing products');
    }
}

async function syncCustomers() {
    showSyncStatus('syncing', 'Syncing Customers...', 'Please wait while we sync customers to ERPNext');

    try {
        const response = await fetch('{{ route("admin.erpnext.sync.customers") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();

        if (data.success) {
            showSyncStatus('success', 'Customers Synced', data.synced + ' customers synced successfully' + (data.failed > 0 ? ', ' + data.failed + ' failed' : ''));
        } else {
            showSyncStatus('error', 'Sync Failed', data.error || 'Failed to sync customers');
        }
    } catch (error) {
        showSyncStatus('error', 'Sync Error', 'An error occurred while syncing customers');
    }
}

async function syncOrders() {
    showSyncStatus('syncing', 'Syncing Orders...', 'Please wait while we sync orders to ERPNext');

    try {
        const response = await fetch('{{ route("admin.erpnext.sync.orders") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();

        if (data.success) {
            showSyncStatus('success', 'Orders Synced', data.synced + ' orders synced successfully' + (data.failed > 0 ? ', ' + data.failed + ' failed' : ''));
        } else {
            showSyncStatus('error', 'Sync Failed', data.error || 'Failed to sync orders');
        }
    } catch (error) {
        showSyncStatus('error', 'Sync Error', 'An error occurred while syncing orders');
    }
}

async function syncInventory() {
    showSyncStatus('syncing', 'Syncing Inventory...', 'Please wait while we sync inventory from ERPNext');

    try {
        const response = await fetch('{{ route("admin.erpnext.sync.inventory") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();

        if (data.success) {
            showSyncStatus('success', 'Inventory Synced', data.synced + ' items updated from ERPNext');
        } else {
            showSyncStatus('error', 'Sync Failed', data.error || 'Failed to sync inventory');
        }
    } catch (error) {
        showSyncStatus('error', 'Sync Error', 'An error occurred while syncing inventory');
    }
}

function showSyncStatus(status, title, message) {
    const statusDiv = document.getElementById('sync-status');
    const statusIcon = document.getElementById('sync-icon');
    const statusTitle = document.getElementById('sync-title');
    const statusMessage = document.getElementById('sync-message');

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
    statusMessage.textContent = message;
}
</script>
@endsection
