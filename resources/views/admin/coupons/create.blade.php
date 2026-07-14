@extends('layouts.admin')
@section('title', 'New Coupon')
@section('content')
<h1 class="text-2xl font-extrabold text-white mb-6">New Coupon</h1>
<form action="{{ route('admin.coupons.store') }}" method="POST" class="bg-white rounded-xl shadow p-6 max-w-2xl space-y-4">
    @csrf
    <div>
        <label class="block mb-1 font-medium text-gray-700">Code</label>
        <input type="text" name="code" value="{{ old('code') }}" class="input-field font-mono uppercase" required placeholder="SAVE10">
        @error('code')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium text-gray-700">Type</label>
            <select name="type" class="input-field">
                <option value="percent">Percent (%)</option>
                <option value="fixed">Fixed amount (৳)</option>
            </select>
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-700">Value</label>
            <input type="number" step="0.01" name="value" value="{{ old('value', 0) }}" class="input-field" required>
            @error('value')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium text-gray-700">Min Subtotal (৳, optional)</label>
            <input type="number" step="0.01" name="min_subtotal" value="{{ old('min_subtotal') }}" class="input-field">
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-700">Max Discount (৳, optional)</label>
            <input type="number" step="0.01" name="max_discount" value="{{ old('max_discount') }}" class="input-field">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium text-gray-700">Starts At (optional)</label>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="input-field">
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-700">Expires At (optional)</label>
            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="input-field">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium text-gray-700">Usage Limit (optional)</label>
            <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" class="input-field">
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                <span class="font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>
    <div class="flex gap-3 pt-2">
        <button class="btn-primary">Save Coupon</button>
        <a href="{{ route('admin.coupons.index') }}" class="btn-outline text-sm">Cancel</a>
    </div>
</form>
@endsection
