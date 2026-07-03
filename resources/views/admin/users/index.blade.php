@extends('layouts.admin')
@section('title', 'User Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage users, roles, and page access</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-48">
                <select name="role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>
            <a href="{{ route('admin.users.permissions') }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Page Permissions
            </a>
            <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Create User
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <p class="text-xs text-gray-500 font-medium uppercase">Total Users</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $users->total() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <p class="text-xs text-gray-500 font-medium uppercase">Admins</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <p class="text-xs text-gray-500 font-medium uppercase">Customers</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ \App\Models\User::where('role', 'customer')->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Quick Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Joined</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $u->role === 'admin' ? 'from-indigo-400 to-purple-500' : ($u->role === 'customer' ? 'from-green-400 to-emerald-500' : 'from-gray-400 to-gray-500') }} flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $u->name }}</div>
                                        @if($u->id === auth()->id())
                                            <span class="text-[10px] text-indigo-500 font-medium">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ match($u->role) { 'admin' => 'bg-indigo-100 text-indigo-700', 'customer' => 'bg-green-100 text-green-700', 'guest' => 'bg-gray-100 text-gray-600', default => 'bg-gray-100 text-gray-600' } }}">
                                    {{ ucfirst($u->role ?? 'customer') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.role', $u) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <select name="role" onchange="this.form.submit()" class="text-xs font-medium border-0 rounded-full px-2.5 py-1 cursor-pointer focus:ring-0
                                            @if($u->role === 'admin') bg-indigo-100 text-indigo-700
                                            @elseif($u->role === 'customer') bg-green-100 text-green-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                            <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="customer" {{ $u->role === 'customer' ? 'selected' : '' }}>Customer</option>
                                            <option value="guest" {{ $u->role === 'guest' ? 'selected' : '' }}>Guest</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $u->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-md text-xs font-medium hover:bg-indigo-100 transition-colors">Edit</a>
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Delete user {{ $u->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-red-50 text-red-600 rounded-md text-xs font-medium hover:bg-red-100 transition-colors">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
