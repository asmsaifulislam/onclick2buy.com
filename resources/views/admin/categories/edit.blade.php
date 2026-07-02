@extends('layouts.admin')
@section('title', 'Edit Category')
@section('content')
<div class="animate-fade-in-up">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900">Edit Category</h1>
    </div>
</div>
<div class="max-w-lg animate-fade-in-up animate-delay-1">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6 md:p-8">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block mb-2 font-medium text-gray-700">Category Name</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="input-field" required>
            @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3" class="input-field" placeholder="Category description...">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium text-gray-700">Image</label>
            <input type="file" name="image" class="input-file w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" accept="image/*">
            @if($category->image)
                <p class="text-xs text-gray-400 mt-1">Current: {{ $category->image }}</p>
            @endif
            @error('image')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="font-medium text-gray-700">Active</span>
            </label>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="btn-outline text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
