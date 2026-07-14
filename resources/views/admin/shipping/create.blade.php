@extends('layouts.admin')
@section('title', 'New Shipping Method')
@section('content')
<h1 class="text-2xl font-extrabold text-white mb-6">New Shipping Method</h1>
<form action="{{ route('admin.shipping.store') }}" method="POST" class="bg-white rounded-xl shadow p-6 max-w-2xl space-y-4">
    @csrf
    <div>
        <label class="block mb-1 font-medium text-gray-700">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="input-field" required placeholder="Express Delivery">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium text-gray-700">Cost (৳)</label>
            <input type="number" step="0.01" name="cost" value="{{ old('cost', 0) }}" class="input-field" required>
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-700">Free Over (৳, optional)</label>
            <input type="number" step="0.01" name="free_over" value="{{ old('free_over') }}" class="input-field" placeholder="500">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 font-medium text-gray-700">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="input-field">
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                <span class="font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>
    <div class="flex gap-3 pt-2">
        <button class="btn-primary">Save Method</button>
        <a href="{{ route('admin.shipping.index') }}" class="btn-outline text-sm">Cancel</a>
    </div>
</form>
@endsection
