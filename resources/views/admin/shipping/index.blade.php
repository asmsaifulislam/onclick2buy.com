@extends('layouts.admin')
@section('title', 'Shipping Methods')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-white">Shipping Methods</h1>
    <a href="{{ route('admin.shipping.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">+ New Method</a>
</div>
@if($methods->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">No shipping methods yet.</div>
@else
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-3">Name</th>
                    <th class="text-left p-3">Cost</th>
                    <th class="text-left p-3">Free Over</th>
                    <th class="text-left p-3">Order</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($methods as $method)
                    <tr class="border-t">
                        <td class="p-3 font-semibold">{{ $method->name }}</td>
                        <td class="p-3">৳{{ number_format($method->cost, 2) }}</td>
                        <td class="p-3">{{ $method->free_over ? '৳' . number_format($method->free_over, 2) : '-' }}</td>
                        <td class="p-3">{{ $method->sort_order }}</td>
                        <td class="p-3">{{ $method->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.shipping.edit', $method) }}" class="text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                            <form action="{{ route('admin.shipping.destroy', $method) }}" method="POST" class="inline" onsubmit="return confirm('Delete this method?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $methods->links() }}</div>
@endif
@endsection
