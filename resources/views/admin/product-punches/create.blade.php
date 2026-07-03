@extends('layouts.admin')
@section('title', 'Add Product Punch')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.product-punches.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Add Product Punch Entry</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.product-punches.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="product_name" value="{{ old('product_name') }}" required placeholder="e.g. Wireless Headphones" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="supplier" value="{{ old('supplier') }}" required placeholder="e.g. TechCorp Ltd" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price ($) <span class="text-red-500">*</span></label>
                    <input type="number" name="unit_price" value="{{ old('unit_price') }}" min="0" step="0.01" required placeholder="0.00" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">QC Remarks</label>
                    <textarea name="qc_remarks" rows="3" placeholder="Quality check notes, defects, condition..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('qc_remarks') }}</textarea>
                </div>
            </div>
            @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            @error('product_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            @error('supplier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            @error('unit_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-6 py-2.5 text-sm font-medium transition-colors">Save Entry</button>
                <a href="{{ route('admin.product-punches.index') }}" class="px-6 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
