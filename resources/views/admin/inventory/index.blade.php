@extends('layouts.admin')
@section('title', 'Inventory')
@section('content')
    <div class="flex justify-between items-center mb-6 animate-fade-in-up">
        <h1 class="text-3xl font-extrabold text-gray-900">Inventory</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.export.inventory') }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fade-in-up animate-delay-1">
        <div class="overflow-x-auto">
            <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-5 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Current Stock</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="table-row border-b border-gray-50">
                        <td class="px-5 py-4 font-medium text-gray-800">{{ $product->name }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $product->category->name ?? 'N/A' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="font-bold text-lg {{ $product->stock > 10 ? 'text-green-600' : ($product->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">{{ $product->stock }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if($product->stock > 10)
                                <span class="order-status-badge bg-green-100 text-green-800">In Stock</span>
                            @elseif($product->stock > 0)
                                <span class="order-status-badge bg-yellow-100 text-yellow-800">Low Stock</span>
                            @else
                                <span class="order-status-badge bg-red-100 text-red-800">Out of Stock</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.inventory.adjust', $product) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors">Adjust Stock</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="mt-6 pagination-custom">{{ $products->links() }}</div>
@endsection
