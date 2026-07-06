@extends('layouts.admin')
@section('title', 'Payment Methods')
@section('page_title', 'Payment Methods')
@section('content')
<div class="grid gap-6">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @foreach($methods as $method)
        <div class="bg-gray-800 rounded-xl p-6 flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-lg font-bold text-white">{{ $method->name }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $method->is_active ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">
                        {{ $method->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-sm text-gray-400">{{ $method->description }}</p>
                @if($method->account_number)
                    <p class="text-sm mt-2"><span class="text-gray-400">Account:</span> <span class="text-white font-mono">{{ $method->account_number }}</span></p>
                @endif
                @if($method->account_name)
                    <p class="text-sm"><span class="text-gray-400">Name:</span> <span class="text-white">{{ $method->account_name }}</span></p>
                @endif
                <p class="text-sm text-gray-500 mt-1">Order: {{ $method->sort_order }} &middot; Code: {{ $method->code }}</p>
            </div>
            <a href="{{ route('admin.payment-methods.edit', $method) }}" class="text-indigo-400 hover:text-indigo-300 font-medium text-sm whitespace-nowrap ml-4">Edit</a>
        </div>
    @endforeach
</div>
@endsection
