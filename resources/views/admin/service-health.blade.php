@extends('layouts.admin')
@section('title', 'Service Health')
@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Service Health</h1>
            <p class="text-gray-500 mt-1">Real-time status of all automation services</p>
        </div>
        <button onclick="refreshAll()" id="refresh-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Refresh All
        </button>
    </div>

    {{-- Overall Status Banner --}}
    @php
        $healthy = collect($services)->where('status', 'healthy')->count();
        $total = count($services);
        $allHealthy = $healthy === $total;
    @endphp
    <div class="mb-8 p-6 rounded-xl {{ $allHealthy ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }}">
        <div class="flex items-center gap-3">
            @if($allHealthy)
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-green-800">All Systems Operational</h3>
                    <p class="text-sm text-green-600">All {{ $total }} services are running properly</p>
                </div>
            @else
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-yellow-800">{{ $total - $healthy }} Service(s) Need Attention</h3>
                    <p class="text-sm text-yellow-600">{{ $healthy }} of {{ $total }} services are healthy</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Service Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="services-grid">
        @foreach($services as $key => $service)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden service-card" data-service="{{ $key }}">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            @if($service['status'] === 'healthy')
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            @elseif($service['status'] === 'warning')
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            @else
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            @endif
                            <h3 class="font-bold text-gray-900">{{ $service['name'] }}</h3>
                        </div>
                        @if($service['critical'])
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">Critical</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mb-4">{{ $service['description'] }}</p>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium
                            {{ $service['status'] === 'healthy' ? 'text-green-600' : '' }}
                            {{ $service['status'] === 'warning' ? 'text-yellow-600' : '' }}
                            {{ $service['status'] === 'error' ? 'text-red-600' : '' }}
                            {{ $service['status'] === 'offline' ? 'text-gray-600' : '' }}">
                            <span class="w-2 h-2 rounded-full
                                {{ $service['status'] === 'healthy' ? 'bg-green-500' : '' }}
                                {{ $service['status'] === 'warning' ? 'bg-yellow-500' : '' }}
                                {{ $service['status'] === 'error' ? 'bg-red-500' : '' }}
                                {{ $service['status'] === 'offline' ? 'bg-gray-400' : '' }}"></span>
                            {{ ucfirst($service['status']) }}
                        </span>
                        <button onclick="testService('{{ $key }}')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                            Test
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Test Result Modal --}}
    <div id="test-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modal-title" class="text-lg font-bold text-gray-900">Test Result</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="modal-content" class="space-y-3"></div>
            <button onclick="closeModal()" class="mt-6 w-full py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">Close</button>
        </div>
    </div>

    <script>
    async function testService(service) {
        const modal = document.getElementById('test-modal');
        const title = document.getElementById('modal-title');
        const content = document.getElementById('modal-content');

        title.textContent = 'Testing ' + service + '...';
        content.innerHTML = '<div class="flex items-center gap-3"><div class="animate-spin w-5 h-5 border-2 border-indigo-600 border-t-transparent rounded-full"></div><span class="text-gray-600">Checking connection...</span></div>';
        modal.classList.remove('hidden');

        try {
            const response = await fetch(`/admin/service-health/test/${service}`);
            const data = await response.json();

            title.textContent = 'Test Result: ' + service;
            let html = `<div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center ${data.status === 'healthy' ? 'bg-green-100' : data.status === 'warning' ? 'bg-yellow-100' : 'bg-red-100'}">
                    ${data.status === 'healthy' ? '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'}
                </div>
                <span class="font-medium ${data.status === 'healthy' ? 'text-green-700' : data.status === 'warning' ? 'text-yellow-700' : 'text-red-700'}">${data.status.toUpperCase()}</span>
            </div>
            <p class="text-gray-700">${data.message}</p>`;
            if (data.fix) {
                html += `<div class="mt-3 p-3 bg-gray-50 rounded-lg"><p class="text-sm font-medium text-gray-600">Fix:</p><code class="text-sm text-indigo-600">${data.fix}</code></div>`;
            }
            content.innerHTML = html;
        } catch (err) {
            title.textContent = 'Test Failed';
            content.innerHTML = `<p class="text-red-600">Error: ${err.message}</p>`;
        }
    }

    function closeModal() {
        document.getElementById('test-modal').classList.add('hidden');
    }

    async function refreshAll() {
        const btn = document.getElementById('refresh-btn');
        btn.innerHTML = '<div class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></div> Checking...';
        btn.disabled = true;

        try {
            const response = await fetch('/admin/service-health/check');
            const services = await response.json();

            document.querySelectorAll('.service-card').forEach(card => {
                const key = card.dataset.service;
                if (services[key]) {
                    const status = services[key].status;
                    const dot = card.querySelector('.w-3');
                    const label = card.querySelector('.font-medium:last-child');

                    dot.className = 'w-3 h-3 rounded-full ' + 
                        (status === 'healthy' ? 'bg-green-500 animate-pulse' : 
                         status === 'warning' ? 'bg-yellow-500' : 'bg-red-500');

                    label.className = 'inline-flex items-center gap-1.5 text-sm font-medium ' +
                        (status === 'healthy' ? 'text-green-600' : 
                         status === 'warning' ? 'text-yellow-600' : 'text-red-600');
                    label.innerHTML = `<span class="w-2 h-2 rounded-full ${status === 'healthy' ? 'bg-green-500' : status === 'warning' ? 'bg-yellow-500' : 'bg-red-500'}"></span>${status.charAt(0).toUpperCase() + status.slice(1)}`;
                }
            });
        } catch (err) {
            console.error(err);
        }

        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Refresh All';
        btn.disabled = false;
    }
    </script>
@endsection
