@extends('layouts.admin')
@section('title', 'Tax Rates')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-white">Tax Rates</h1>
    <a href="{{ route('admin.tax.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">+ New Tax Rate</a>
</div>
@if($rates->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">No tax rates yet.</div>
@else
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-3">Name</th>
                    <th class="text-left p-3">Rate (%)</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rates as $rate)
                    <tr class="border-t">
                        <td class="p-3 font-semibold">{{ $rate->name }}</td>
                        <td class="p-3">{{ number_format($rate->rate, 2) }}%</td>
                        <td class="p-3">{{ $rate->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.tax.edit', $rate) }}" class="text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                            <form action="{{ route('admin.tax.destroy', $rate) }}" method="POST" class="inline" onsubmit="return confirm('Delete this tax rate?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $rates->links() }}</div>
@endif
@endsection
