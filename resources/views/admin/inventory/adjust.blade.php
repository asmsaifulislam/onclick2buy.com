@extends('layouts.admin')
@section('title', 'Adjust Stock')
@section('content')
    <div class="flex justify-between items-center mb-6 animate-fade-in-up">
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Inventory
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900">Adjust Stock: {{ $product->name }}</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in-up animate-delay-1">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="{{ route('admin.inventory.store', $product) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <p class="text-sm text-gray-500 mb-1">Current Stock</p>
                        <p class="text-3xl font-bold {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $product->stock }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-700">
                                <input type="radio" name="type" value="add" checked class="accent-indigo-600">
                                <span class="text-sm font-medium">Add Stock</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-700">
                                <input type="radio" name="type" value="subtract" class="accent-indigo-600">
                                <span class="text-sm font-medium">Remove Stock</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-700">
                                <input type="radio" name="type" value="set" class="accent-indigo-600">
                                <span class="text-sm font-medium">Set to Exact</span>
                            </label>
                        </div>
                        @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" id="quantity" min="0" value="{{ old('quantity') }}" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                        <textarea name="notes" id="notes" rows="2" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                        @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary inline-flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Update Stock
                    </button>
                </form>
            </div>
        </div>
        <div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Recent Transactions</h3>
                @forelse($transactions as $txn)
                    <div class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($txn->type === 'add')
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </span>
                            @elseif($txn->type === 'subtract')
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">
                                {{ ucfirst($txn->type) }} &mdash;
                                <span class="font-bold">{{ $txn->quantity }}</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $txn->previous_stock }} &rarr; {{ $txn->new_stock }}
                                &middot; {{ $txn->created_at->diffForHumans() }}
                            </p>
                            @if($txn->notes)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $txn->notes }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No transactions yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
