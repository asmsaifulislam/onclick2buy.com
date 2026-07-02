@extends('layouts.admin')
@section('title', 'Create Product')
@section('content')
<div class="animate-fade-in-up">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900">Create Product</h1>
    </div>
</div>
<div class="max-w-3xl animate-fade-in-up animate-delay-1">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6 md:p-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div class="mb-1">
                <label class="block mb-2 font-medium text-gray-700">Product Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-field" required>
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-1">
                <label class="block mb-2 font-medium text-gray-700">Category</label>
                <select name="category_id" class="input-field" required>
                    <option value="">Select a category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-1">
                <label class="block mb-2 font-medium text-gray-700">Price ($)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="input-field" required placeholder="0.00">
                @error('price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-1">
                <label class="block mb-2 font-medium text-gray-700">Sale Price ($)</label>
                <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price') }}" class="input-field" placeholder="0.00">
                @error('sale_price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-1">
                <label class="block mb-2 font-medium text-gray-700">Stock</label>
                <input type="number" name="stock" value="{{ old('stock', 0) }}" class="input-field" required>
                @error('stock')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-1">
                <label class="block mb-2 font-medium text-gray-700">SKU</label>
                <input type="text" name="sku" value="{{ old('sku') }}" class="input-field" placeholder="e.g. PROD-001">
                @error('sku')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mb-4 mt-4">
            <label class="block mb-2 font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4" class="input-field" placeholder="Product description...">{{ old('description') }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium text-gray-700">Images</label>
            <input type="file" name="images[]" multiple class="input-file w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" accept="image/*">
            @error('images.*')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
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
                Create Product
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn-outline text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
