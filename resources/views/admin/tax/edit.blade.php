@extends('layouts.admin')
@section('title', 'Edit Tax Rate')
@section('content')
<h1 class="text-2xl font-extrabold text-white mb-6">Edit Tax Rate</h1>
<form action="{{ route('admin.tax.update', $tax) }}" method="POST" class="bg-white rounded-xl shadow p-6 max-w-2xl space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="block mb-1 font-medium text-gray-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $tax->name) }}" class="input-field" required>
    </div>
    <div>
        <label class="block mb-1 font-medium text-gray-700">Rate (%)</label>
        <input type="number" step="0.01" name="rate" value="{{ old('rate', $tax->rate) }}" class="input-field" required>
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tax->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600">
            <span class="font-medium text-gray-700">Active</span>
        </label>
    </div>
    <div class="flex gap-3 pt-2">
        <button class="btn-primary">Update Tax Rate</button>
        <a href="{{ route('admin.tax.index') }}" class="btn-outline text-sm">Cancel</a>
    </div>
</form>
@endsection
