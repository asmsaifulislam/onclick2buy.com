@extends('layouts.admin')
@section('title', 'Coupons')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-white">Coupons</h1>
    <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">+ New Coupon</a>
</div>
@if($coupons->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">No coupons yet.</div>
@else
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-3">Code</th>
                    <th class="text-left p-3">Type</th>
                    <th class="text-left p-3">Value</th>
                    <th class="text-left p-3">Min Subtotal</th>
                    <th class="text-left p-3">Used</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                    <tr class="border-t">
                        <td class="p-3 font-mono font-semibold">{{ $coupon->code }}</td>
                        <td class="p-3">{{ $coupon->type === 'percent' ? 'Percent' : 'Fixed' }}</td>
                        <td class="p-3">{{ $coupon->type === 'percent' ? $coupon->value . '%' : '৳' . number_format($coupon->value, 2) }}</td>
                        <td class="p-3">৳{{ number_format($coupon->min_subtotal, 2) }}</td>
                        <td class="p-3">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</td>
                        <td class="p-3">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
@endif
@endsection
