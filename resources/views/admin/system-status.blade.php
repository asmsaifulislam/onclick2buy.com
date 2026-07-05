@extends('layouts.admin')
@section('title', 'System Status')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">System Status</h1>
        <p class="text-sm text-gray-500">Health, connections, resources & AI promotions</p>
    </div>
    <button onclick="location.reload()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

@php
    $healthy = collect($services)->where('status', 'healthy')->count();
    $total = count($services);
    $offline = collect($services)->where('status', 'offline')->count();
    $errors = collect($services)->where('status', 'error')->count();
@endphp

{{-- Top Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-3xl font-bold text-gray-900">{{ $total }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Services</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-3xl font-bold text-green-600">{{ $healthy }}</p>
        <p class="text-xs text-gray-500 mt-1">Healthy</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-3xl font-bold text-yellow-600">{{ $offline }}</p>
        <p class="text-xs text-gray-500 mt-1">Offline</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-3xl font-bold text-red-600">{{ $errors }}</p>
        <p class="text-xs text-gray-500 mt-1">Errors</p>
    </div>
</div>

{{-- Tabs --}}
<div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-xl w-fit text-sm">
    <button onclick="showTab('health')" class="stab active" data-t="health">Health</button>
    <button onclick="showTab('resources')" class="stab" data-t="resources">Resources</button>
    <button onclick="showTab('connections')" class="stab" data-t="connections">Connections</button>
    <button onclick="showTab('config')" class="stab" data-t="config">Config</button>
    <button onclick="showTab('promo')" class="stab" data-t="promo">AI Promos</button>
</div>

{{-- TAB: Health --}}
<div id="tab-health" class="spanel">
    @if($healthy === $total)
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div><h3 class="font-bold text-green-800 text-sm">All Systems Operational</h3><p class="text-xs text-green-600">{{ $total }}/{{ $total }} services running</p></div>
    </div>
    @else
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl flex items-center gap-3">
        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        </div>
        <div><h3 class="font-bold text-yellow-800 text-sm">{{ $total - $healthy }} service(s) need attention</h3><p class="text-xs text-yellow-600">{{ $healthy }} healthy, {{ $offline }} offline, {{ $errors }} errors</p></div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($services as $key => $s)
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $s['status']==='healthy'?'bg-green-500':($s['status']==='warning'?'bg-yellow-500':'bg-red-500') }}"></span>
                    <h3 class="font-semibold text-gray-900 text-sm">{{ $s['name'] }}</h3>
                </div>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase {{ $s['status']==='healthy'?'bg-green-100 text-green-700':($s['status']==='warning'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') }}">{{ $s['status'] }}</span>
            </div>
            <div class="space-y-1 text-xs text-gray-500">
                <div class="flex justify-between"><span>Type</span><span class="text-gray-700">{{ $s['type'] }}</span></div>
                <div class="flex justify-between"><span>IP</span><span class="font-mono text-gray-700">{{ $s['ip'] }}</span></div>
                <div class="flex justify-between"><span>Port</span><span class="font-mono text-gray-700">{{ $s['port'] }}</span></div>
                <div class="flex justify-between"><span>Path</span><span class="font-mono text-gray-700 truncate max-w-[150px]">{{ $s['path'] }}</span></div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 text-xs text-gray-400">{{ $s['details'] }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- TAB: Resources --}}
<div id="tab-resources" class="spanel hidden">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- CPU --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                </div>
                <div><h3 class="font-bold text-gray-900 text-sm">CPU</h3><p class="text-xs text-gray-500">{{ $system['cpu']['cores'] }} cores</p></div>
            </div>
            <div class="mb-2">
                <div class="flex justify-between text-xs mb-1"><span class="text-gray-500">Usage</span><span class="font-bold {{ $system['cpu']['usage']>80?'text-red-600':($system['cpu']['usage']>60?'text-yellow-600':'text-green-600') }}">{{ $system['cpu']['usage'] }}%</span></div>
                <div class="w-full bg-gray-100 rounded-full h-2.5"><div class="h-2.5 rounded-full {{ $system['cpu']['usage']>80?'bg-red-500':($system['cpu']['usage']>60?'bg-yellow-500':'bg-green-500') }}" style="width:{{ $system['cpu']['usage'] }}%"></div></div>
            </div>
            <p class="text-[10px] text-gray-400 mt-2 truncate">{{ $system['cpu']['model'] }}</p>
        </div>

        {{-- Memory --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div><h3 class="font-bold text-gray-900 text-sm">Memory</h3><p class="text-xs text-gray-500">{{ $system['memory']['used'] }} / {{ $system['memory']['total'] }}</p></div>
            </div>
            <div class="mb-2">
                <div class="flex justify-between text-xs mb-1"><span class="text-gray-500">Usage</span><span class="font-bold {{ $system['memory']['usage_percent']>80?'text-red-600':($system['memory']['usage_percent']>60?'text-yellow-600':'text-green-600') }}">{{ $system['memory']['usage_percent'] }}%</span></div>
                <div class="w-full bg-gray-100 rounded-full h-2.5"><div class="h-2.5 rounded-full {{ $system['memory']['usage_percent']>80?'bg-red-500':($system['memory']['usage_percent']>60?'bg-yellow-500':'bg-green-500') }}" style="width:{{ $system['memory']['usage_percent'] }}%"></div></div>
            </div>
            <div class="flex justify-between text-[10px] text-gray-400 mt-2"><span>Free: {{ $system['memory']['free'] }}</span><span>Used: {{ $system['memory']['used'] }}</span></div>
        </div>

        {{-- Disk --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                </div>
                <div><h3 class="font-bold text-gray-900 text-sm">Disk</h3><p class="text-xs text-gray-500">{{ $system['disk']['used'] }} / {{ $system['disk']['total'] }}</p></div>
            </div>
            <div class="mb-2">
                <div class="flex justify-between text-xs mb-1"><span class="text-gray-500">Usage</span><span class="font-bold {{ $system['disk']['usage_percent']>80?'text-red-600':($system['disk']['usage_percent']>60?'text-yellow-600':'text-green-600') }}">{{ $system['disk']['usage_percent'] }}%</span></div>
                <div class="w-full bg-gray-100 rounded-full h-2.5"><div class="h-2.5 rounded-full {{ $system['disk']['usage_percent']>80?'bg-red-500':($system['disk']['usage_percent']>60?'bg-yellow-500':'bg-green-500') }}" style="width:{{ $system['disk']['usage_percent'] }}%"></div></div>
            </div>
            <div class="flex justify-between text-[10px] text-gray-400 mt-2"><span>Free: {{ $system['disk']['free'] }}</span><span>Used: {{ $system['disk']['used'] }}</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4">System Load</h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 bg-gray-50 rounded-lg"><p class="text-2xl font-bold text-gray-900">{{ $system['load']['1min'] }}</p><p class="text-[10px] text-gray-500">1 min</p></div>
                <div class="text-center p-3 bg-gray-50 rounded-lg"><p class="text-2xl font-bold text-gray-900">{{ $system['load']['5min'] }}</p><p class="text-[10px] text-gray-500">5 min</p></div>
                <div class="text-center p-3 bg-gray-50 rounded-lg"><p class="text-2xl font-bold text-gray-900">{{ $system['load']['15min'] }}</p><p class="text-[10px] text-gray-500">15 min</p></div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4">Server Info</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">OS</span><span class="font-medium">{{ $system['os'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">PHP</span><span class="font-medium">{{ $system['php_version'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Laravel</span><span class="font-medium">{{ $system['laravel_version'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Uptime</span><span class="font-medium">{{ $system['uptime'] }}</span></div>
            </div>
        </div>
    </div>
</div>

{{-- TAB: Connections --}}
<div id="tab-connections" class="spanel hidden">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Service</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Host</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Port</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">DB / User</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
            </tr></thead>
            <tbody>
                @foreach($connections as $c)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $c['service'] }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $c['type'] }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $c['host'] }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $c['port'] }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $c['database'] }} / {{ $c['username'] }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 text-xs font-medium {{ $c['status']?'text-green-600':'text-red-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $c['status']?'bg-green-500':'bg-red-500' }}"></span>
                            {{ $c['status']?'Connected':'Failed' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- TAB: Config --}}
<div id="tab-config" class="spanel hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4">Environment File</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between"><span class="text-gray-500">Path</span><span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-700">{{ $config['env_file']['path'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Exists</span><span class="{{ $config['env_file']['exists']?'text-green-600':'text-red-600' }}">{{ $config['env_file']['exists']?'Yes':'No' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Readable</span><span class="{{ $config['env_file']['readable']?'text-green-600':'text-red-600' }}">{{ $config['env_file']['readable']?'Yes':'No' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Writable</span><span class="{{ $config['env_file']['writable']?'text-green-600':'text-red-600' }}">{{ $config['env_file']['writable']?'Yes':'No' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Size</span><span class="font-medium">{{ $config['env_file']['size'] }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4">Application Config</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between"><span class="text-gray-500">APP_URL</span><span class="font-medium">{{ $config['app_url'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">APP_ENV</span><span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $config['app_env']==='production'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $config['app_env'] }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">APP_DEBUG</span><span class="{{ $config['app_debug']?'text-yellow-600':'text-green-600' }}">{{ $config['app_debug']?'ON':'OFF' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Timezone</span><span class="font-medium">{{ $config['timezone'] }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4">Directories</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between p-2 bg-gray-50 rounded"><span class="font-mono text-gray-600">/config</span><span class="text-green-600">{{ $config['config_dir']['files'] }} files</span></div>
                <div class="flex justify-between p-2 bg-gray-50 rounded"><span class="font-mono text-gray-600">/public</span><span class="{{ $config['public_dir']['writable']?'text-green-600':'text-red-600' }}">{{ $config['public_dir']['writable']?'Writable':'Read-only' }}</span></div>
                <div class="flex justify-between p-2 bg-gray-50 rounded"><span class="font-mono text-gray-600">/storage/cache</span><span class="text-gray-600">{{ $config['storage_dir']['cache_size'] }}</span></div>
                <div class="flex justify-between p-2 bg-gray-50 rounded"><span class="font-mono text-gray-600">/storage/logs</span><span class="text-gray-600">{{ $config['storage_dir']['log_size'] }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4">Service IPs & Ports</h3>
            <div class="space-y-1.5 text-xs font-mono">
                @foreach($services as $s)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <span class="text-gray-600">{{ $s['name'] }}</span>
                    <span class="text-indigo-600 font-bold">{{ $s['ip'] }}:{{ $s['port'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- TAB: AI Promotions --}}
<div id="tab-promo" class="spanel hidden">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-gray-900">AI-Powered Promotions</h2>
        <button onclick="document.getElementById('promo-modal').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg text-sm font-medium flex items-center gap-2 hover:from-purple-700 hover:to-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Create Promotion
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($promotions as $p)
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-2">
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $p['type']==='flash_sale'?'bg-red-100 text-red-700':($p['type']==='combo'?'bg-purple-100 text-purple-700':'bg-blue-100 text-blue-700') }}">{{ ucwords(str_replace('_',' ',$p['type'])) }}</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $p['status']==='active'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($p['status']) }}</span>
            </div>
            <h3 class="font-bold text-gray-900 text-sm mt-2">{{ $p['title'] }}</h3>
            <div class="space-y-1 text-xs text-gray-600 mt-2">
                <div class="flex justify-between"><span>Discount</span><span class="font-bold text-indigo-600">{{ $p['discount'] }}% OFF</span></div>
                <div class="flex justify-between"><span>Products</span><span>{{ count($p['products']) }} items</span></div>
                <div class="flex justify-between"><span>Period</span><span class="text-gray-400">{{ $p['start_date'] }} - {{ $p['end_date'] }}</span></div>
            </div>
            @if(isset($p['ai_suggestion']))
            <div class="mt-3 p-2.5 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-100">
                <p class="text-[10px] font-bold text-purple-700 mb-1">AI Suggestion</p>
                <p class="text-[10px] text-gray-600 leading-relaxed">{{ $p['ai_suggestion'] }}</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- Create Promotion Modal --}}
<div id="promo-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900">Create AI Promotion</h3>
            <button onclick="document.getElementById('promo-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="promo-form" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-600">Promotion Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1" required>
                    <option value="flash_sale">Flash Sale</option>
                    <option value="discount">Discount</option>
                    <option value="combo">Combo Deal</option>
                    <option value="bundle">Bundle Offer</option>
                    <option value="seasonal">Seasonal</option>
                    <option value="clearance">Clearance</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Title</label>
                <input type="text" name="title" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1" required placeholder="e.g. Summer Sale">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-600">Discount %</label>
                    <input type="number" name="discount" min="1" max="90" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1" required placeholder="25">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">Products</label>
                    <input type="text" name="products" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1" required placeholder="1,2,3">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-600">Start Date</label>
                    <input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1" required>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">End Date</label>
                    <input type="date" name="end_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1" required>
                </div>
            </div>
            <div id="promo-result" class="hidden"></div>
            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium text-sm hover:from-purple-700 hover:to-indigo-700">Generate with AI</button>
        </form>
    </div>
</div>

<style>
.stab{padding:8px 16px;border-radius:8px;font-weight:500;color:#6b7280;transition:all .15s}
.stab:hover{color:#111827}
.stab.active{background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.1);color:#4338ca;font-weight:600}
</style>

<script>
function showTab(t){
    document.querySelectorAll('.spanel').forEach(e=>e.classList.add('hidden'));
    document.getElementById('tab-'+t).classList.remove('hidden');
    document.querySelectorAll('.stab').forEach(b=>{b.classList.remove('active');if(b.dataset.t===t)b.classList.add('active')});
}
document.getElementById('promo-form').addEventListener('submit',async function(e){
    e.preventDefault();
    const fd=new FormData(this);
    fd.set('products',JSON.stringify(fd.get('products').split(',').map(Number)));
    const r=await fetch('{{ route("admin.system-status.create-promo") }}',{method:'POST',body:fd,headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}});
    const d=await r.json();
    const el=document.getElementById('promo-result');
    el.classList.remove('hidden');
    el.innerHTML=d.success?'<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">'+d.message+'</div>':'<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">'+d.message+'</div>';
    if(d.success)setTimeout(()=>location.reload(),1500);
});
</script>
@endsection
