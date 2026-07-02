@extends('layouts.admin')
@section('title', 'Create Category')
@section('content')
<div class="animate-fade-in-up">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900">Create Category</h1>
    </div>
</div>
<div class="max-w-lg animate-fade-in-up animate-delay-1">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6 md:p-8">
        @csrf
        <div class="mb-4">
            <label class="block mb-2 font-medium text-gray-700">Category Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="input-field" required placeholder="e.g. Electronics">
            @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3" class="input-field" placeholder="Category description...">{{ old('description') }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium text-gray-700">Image</label>
            <input type="file" name="image" class="input-file w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" accept="image/*">
            @error('image')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="font-medium text-gray-700">Active</span>
            </label>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="btn-outline text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
