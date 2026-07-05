@extends('layouts.admin')

@section('title', 'Product Recommendations')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Product Recommendations</h1>
            <p class="text-gray-500 mt-1">AI-powered product suggestions using Surprise</p>
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

    {{-- Model Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Algorithm</p>
                    <p id="model-algorithm" class="text-xl font-bold text-gray-900">{{ config('recommendations.algorithm') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">RMSE</p>
                    <p id="model-rmse" class="text-xl font-bold text-gray-900">-</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Ratings</p>
                    <p id="model-ratings" class="text-xl font-bold text-gray-900">-</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Users</p>
                    <p id="model-users" class="text-xl font-bold text-gray-900">-</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Train Model --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Train Model</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Algorithm</label>
                    <select id="algorithm-select" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="SVD">SVD (Singular Value Decomposition)</option>
                        <option value="KNN">KNN (K-Nearest Neighbors)</option>
                        <option value="NMF">NMF (Non-negative Matrix Factorization)</option>
                    </select>
                </div>
                <button onclick="trainModel()" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                    Train Model
                </button>
                <p class="text-sm text-gray-500">Training may take a few minutes depending on data size.</p>
            </div>
        </div>

        {{-- Sync Data --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Data Management</h2>
            <div class="space-y-3">
                <button onclick="syncRatings()" class="w-full flex items-center gap-3 px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Ratings from Database
                </button>
                <button onclick="getModelInfo()" class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Refresh Model Info
                </button>
            </div>
        </div>
    </div>

    {{-- Setup Guide --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Setup Guide</h2>
        <div class="prose prose-sm max-w-none text-gray-600">
            <h3 class="text-base font-semibold text-gray-900 mt-4">Quick Start</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li>Start the recommendation engine: <code class="bg-gray-100 px-1 rounded">docker compose -f recommendation/docker-compose.recommendation.yml up -d</code></li>
                <li>Sync ratings from database</li>
                <li>Train the model</li>
                <li>Test connection</li>
            </ol>

            <h3 class="text-base font-semibold text-gray-900 mt-4">Algorithms</h3>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>SVD:</strong> Best for sparse data, fast training</li>
                <li><strong>KNN:</strong> Good for finding similar users</li>
                <li><strong>NMF:</strong> Non-negative factorization, interpretable</li>
            </ul>

            <h3 class="text-base font-semibold text-gray-900 mt-4">How It Works</h3>
            <ul class="list-disc list-inside space-y-1">
                <li>User ratings are collected from reviews</li>
                <li>Surprise learns user preferences</li>
                <li>Personalized recommendations are generated</li>
                <li>Fallback to popular products for new users</li>
            </ul>
        </div>
    </div>
</div>

<script>
async function testConnection() {
    showStatus('syncing', 'Testing Connection...', 'Please wait');

    try {
        const response = await fetch('{{ route("admin.recommendations.test") }}');
        const data = await response.json();

        if (data.status === 'success') {
            showStatus('success', 'Connection Successful', data.message);
        } else {
            showStatus('error', 'Connection Failed', data.message);
        }
    } catch (error) {
        showStatus('error', 'Connection Error', 'Failed to connect to recommendation engine');
    }
}

async function trainModel() {
    const algorithm = document.getElementById('algorithm-select').value;
    showStatus('syncing', 'Training Model...', 'This may take a few minutes');

    try {
        const response = await fetch('{{ route("admin.recommendations.train") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ algorithm }),
        });
        const data = await response.json();

        if (data.success) {
            showStatus('success', 'Training Complete', 'Model trained successfully');
            updateModelInfo(data.metrics);
        } else {
            showStatus('error', 'Training Failed', data.message || 'Failed to train model');
        }
    } catch (error) {
        showStatus('error', 'Training Error', 'An error occurred');
    }
}

async function syncRatings() {
    showStatus('syncing', 'Syncing Ratings...', 'Please wait');

    try {
        const response = await fetch('{{ route("admin.recommendations.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();

        if (data.success) {
            showStatus('success', 'Sync Complete', data.count + ' ratings synced');
        } else {
            showStatus('error', 'Sync Failed', data.message);
        }
    } catch (error) {
        showStatus('error', 'Sync Error', 'An error occurred');
    }
}

async function getModelInfo() {
    try {
        const response = await fetch('{{ route("admin.recommendations.model") }}');
        const data = await response.json();
        updateModelInfo(data);
    } catch (error) {
        console.error('Failed to get model info');
    }
}

function updateModelInfo(data) {
    if (data.algorithm) {
        document.getElementById('model-algorithm').textContent = data.algorithm;
    }
    if (data.rmse) {
        document.getElementById('model-rmse').textContent = data.rmse.toFixed(4);
    }
    if (data.total_ratings) {
        document.getElementById('model-ratings').textContent = data.total_ratings.toLocaleString();
    }
    if (data.total_users) {
        document.getElementById('model-users').textContent = data.total_users.toLocaleString();
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

// Load model info on page load
getModelInfo();
</script>
@endsection
