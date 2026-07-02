@extends('layouts.admin')
@section('title', 'Bulk Upload & Sync')
@section('content')
<div class="animate-fade-in-up">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Bulk Upload & Sync</h1>
    <p class="text-gray-500 mb-8">Upload product images to auto-create products, or run sync operations.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-1">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Auto-Create Products from Images
        </h2>
        <p class="text-sm text-gray-500 mb-4">Upload one or more product images. Products will be created automatically with the image filename as the product name.</p>
        <form action="{{ route('admin.bulkupload.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block mb-2 font-medium text-gray-700">Select Images</label>
                <input type="file" name="images[]" multiple accept="image/*" required class="input-field" onchange="previewImages(this)">
                <div id="imagePreview" class="grid grid-cols-4 gap-2 mt-2"></div>
                @error('images.*')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Category</label>
                    <select name="category_id" required class="input-field">
                        <option value="">Select...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Default Price ($)</label>
                    <input type="number" step="0.01" name="default_price" value="29.99" required class="input-field">
                </div>
            </div>
            <div class="mb-4">
                <label class="block mb-2 font-medium text-gray-700">Default Stock</label>
                <input type="number" name="default_stock" value="10" class="input-field">
            </div>
            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Upload & Create Products
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Synchronization Tools
        </h2>
        <p class="text-sm text-gray-500 mb-6">Run sync operations to clean up and maintain your product data.</p>
        <div class="space-y-4">
            <form action="{{ route('admin.bulkupload.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="stock">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div>
                        <p class="font-medium text-gray-800">Auto-deactivate out-of-stock</p>
                        <p class="text-sm text-gray-500">Deactivate products with 0 stock</p>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors text-sm font-medium w-full sm:w-auto">Run Sync</button>
                </div>
            </form>
            <form action="{{ route('admin.bulkupload.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="prices">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div>
                        <p class="font-medium text-gray-800">Fix invalid sale prices</p>
                        <p class="text-sm text-gray-500">Remove sale prices &ge; regular price</p>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors text-sm font-medium w-full sm:w-auto">Run Sync</button>
                </div>
            </form>
            <form action="{{ route('admin.bulkupload.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="categories">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div>
                        <p class="font-medium text-gray-800">Assign uncategorized products</p>
                        <p class="text-sm text-gray-500">Move products with no category to "Uncategorized"</p>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors text-sm font-medium w-full sm:w-auto">Run Sync</button>
                </div>
            </form>
            <form action="{{ route('admin.bulkupload.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="recalculate">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div>
                        <p class="font-medium text-gray-800">Recalculate stock counts</p>
                        <p class="text-sm text-gray-500">Sync actual stock from order history</p>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors text-sm font-medium w-full sm:w-auto">Run Sync</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `<img src="${e.target.result}" class="h-20 w-full object-cover rounded-lg shadow-sm"><div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors rounded-lg"></div>`;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endsection
