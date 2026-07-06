@extends('layouts.admin')
@section('title', 'Backup & Restore')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Backup & Restore</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-5 border border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Database Size</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $dbSize }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Storage Size</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $storageSize }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Saved Backups</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ count($backups) }}</p>
        </div>
    </div>

    {{-- Create Backup --}}
    <div class="bg-white rounded-xl shadow border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Create Backup</h2>
        <div class="grid grid-cols-2 gap-4">
            <form action="{{ route('admin.backup.create') }}" method="POST">
                @csrf
                <p class="text-sm text-gray-600 mb-3">Database, uploaded files, and .env config only.</p>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Data Backup
                </button>
            </form>
            <form action="{{ route('admin.backup.create-full') }}" method="POST">
                @csrf
                <p class="text-sm text-gray-600 mb-3">Entire project (code + data). Excludes vendor, node_modules, .git.</p>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    Full Site Backup
                </button>
            </form>
        </div>
    </div>

    {{-- Restore --}}
    <div class="bg-white rounded-xl shadow border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Restore from Backup</h2>
        <p class="text-sm text-gray-600 mb-4">Upload a previous backup zip to restore the database, files, and .env config.</p>
        <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center gap-4">
                <input type="file" name="backup_file" accept=".zip" required class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white font-medium rounded-lg hover:bg-amber-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Restore
                </button>
            </div>
        </form>
    </div>

    {{-- Existing Backups --}}
    <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Saved Backups</h2>
        @if(count($backups) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Filename</th>
                        <th class="py-3 px-4">Size</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium text-gray-900">{{ $backup['name'] }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $backup['size'] }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $backup['date'] }}</td>
                        <td class="py-3 px-4 flex gap-2">
                            <a href="{{ route('admin.backup.download', $backup['name']) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded hover:bg-green-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download
                            </a>
                            <form action="{{ route('admin.backup.delete', $backup['name']) }}" method="POST" onsubmit="return confirm('Delete this backup?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-sm py-4 text-center">No backups yet. Create your first backup above.</p>
        @endif
    </div>
</div>
@endsection
