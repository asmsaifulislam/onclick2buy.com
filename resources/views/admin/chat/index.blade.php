@extends('layouts.admin')

@section('title', 'Live Chat')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Live Chat</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $sessions->where('status', 'open')->count() }} open conversations</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($sessions->isEmpty())
            <div class="p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-lg font-medium">No chat sessions yet</p>
                <p class="text-sm mt-1">When visitors start a conversation, it will appear here.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($sessions as $s)
                    <a href="{{ route('admin.chat.show', $s) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors {{ $s->status === 'closed' ? 'opacity-60' : '' }}">
                        <div class="relative flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $s->status === 'open' ? 'from-green-400 to-emerald-500' : 'from-gray-300 to-gray-400' }} flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($s->display_name, 0, 1)) }}
                            </div>
                            @if($s->status === 'open')
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $s->display_name }}</h3>
                                <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $s->last_message_at ? $s->last_message_at->diffForHumans() : '' }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <p class="text-sm text-gray-500 truncate">{{ $s->latestMessage?->message ?? 'No messages yet' }}</p>
                                @if($s->status === 'open' && $s->messages()->where('is_admin', false)->where('is_read', false)->count() > 0)
                                    <span class="ml-2 flex-shrink-0 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $s->messages()->where('is_admin', false)->where('is_read', false)->count() }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                @if($s->user_id)
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Registered</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Guest</span>
                                @endif
                                @if($s->status === 'open')
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Open</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Closed</span>
                                @endif
                                @if($s->visitor_email)
                                    <span class="text-xs text-gray-400">{{ $s->visitor_email }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
