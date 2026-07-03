@extends('layouts.admin')
@section('title', 'Page Permissions')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Page Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">Control which pages each role can access</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.permissions') }}">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Page</th>
                            @foreach($roles as $role)
                                <th class="px-5 py-4 text-center text-xs font-semibold text-gray-500 uppercase">
                                    <span class="inline-block px-2.5 py-1 rounded-full {{ $role === 'admin' ? 'bg-indigo-100 text-indigo-700' : ($role === 'customer' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($role) }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pages as $key => $label)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3 text-sm font-medium text-gray-800">{{ $label }}</td>
                                @foreach($roles as $role)
                                    <td class="px-5 py-3 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="permissions[{{ $role }}][]" value="{{ $key }}"
                                                {{ in_array($key, $permissions[$role] ?? []) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-6">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-6 py-2.5 text-sm font-medium transition-colors">Save Permissions</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
