<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function index()
    {
        $sessions = ChatSession::with('latestMessage', 'user:id,name,email')
            ->orderBy('last_message_at', 'desc')
            ->get();

        return view('admin.chat.index', compact('sessions'));
    }

    public function show(ChatSession $session)
    {
        $session->load(['messages.user:id,name', 'user:id,name,email']);

        $session->messages()
            ->where('is_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.chat.show', compact('session'));
    }

    public function send(Request $request, ChatSession $session): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = $session->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin' => true,
            'is_read' => true,
        ]);

        $session->update(['last_message_at' => now()]);

        return response()->json([
            'id' => $message->id,
            'message' => $message->message,
            'is_admin' => true,
            'sender' => auth()->user()->name,
            'created_at' => $message->created_at->format('h:i A'),
        ]);
    }

    public function poll(Request $request, ChatSession $session): JsonResponse
    {
        $lastId = $request->input('last_id', 0);

        $newMessages = $session->messages()
            ->where('id', '>', $lastId)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        $session->messages()
            ->where('is_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'messages' => $newMessages->map(fn($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_admin' => $m->is_admin,
                'sender' => $m->user ? $m->user->name : ($m->is_admin ? 'Support' : 'Visitor'),
                'created_at' => $m->created_at->format('h:i A'),
            ]),
        ]);
    }

    public function close(ChatSession $session)
    {
        $session->update(['status' => 'closed']);
        return redirect()->route('admin.chat.index')->with('success', 'Chat session closed.');
    }

    public function reopen(ChatSession $session)
    {
        $session->update(['status' => 'open']);
        return redirect()->route('admin.chat.show', $session)->with('success', 'Chat session reopened.');
    }

    public function unreadCount(): JsonResponse
    {
        $count = ChatSession::where('status', 'open')
            ->whereHas('messages', function ($q) {
                $q->where('is_admin', false)->where('is_read', false);
            })
            ->count();

        return response()->json(['count' => $count]);
    }
}
