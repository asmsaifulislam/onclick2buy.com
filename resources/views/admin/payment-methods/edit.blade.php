@extends('layouts.admin')
@section('title', 'Edit ' . $paymentMethod->name)
@section('page_title', 'Edit Payment Method: ' . $paymentMethod->name)
@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod) }}" class="bg-gray-800 rounded-xl p-6 space-y-5">
        @csrf @method('PUT')
        <div>
            <label class="block mb-1 font-medium text-gray-300">Name</label>
            <input type="text" name="name" value="{{ old('name', $paymentMethod->name) }}" class="input-field w-full" required>
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-300">Description</label>
            <textarea name="description" rows="2" class="input-field w-full">{{ old('description', $paymentMethod->description) }}</textarea>
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-300">Account Number</label>
            <input type="text" name="account_number" value="{{ old('account_number', $paymentMethod->account_number) }}" class="input-field w-full">
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-300">Account Name</label>
            <input type="text" name="account_name" value="{{ old('account_name', $paymentMethod->account_name) }}" class="input-field w-full">
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-300">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $paymentMethod->sort_order) }}" class="input-field w-24">
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-300">Instructions <span class="text-gray-500 text-xs">(one step per line)</span></label>
            <textarea name="instructions[]" rows="6" class="input-field w-full font-mono text-sm">{{ old('instructions', $paymentMethod->instructions) ? implode("\n", (array)old('instructions', $paymentMethod->instructions)) : '' }}</textarea>
        </div>
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ $paymentMethod->is_active ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
            <span class="text-gray-300">Active</span>
        </label>
        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Update</button>
            <a href="{{ route('admin.payment-methods.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
