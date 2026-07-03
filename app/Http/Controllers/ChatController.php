<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function session(Request $request): JsonResponse
    {
        $visitorId = $request->cookie('chat_visitor_id') ?: Str::uuid()->toString();

        $session = ChatSession::where('visitor_id', $visitorId)
            ->orWhere('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (!$session) {
            $session = ChatSession::create([
                'user_id' => auth()->id(),
                'visitor_id' => $visitorId,
                'visitor_name' => $request->input('name', auth()->user()->name ?? null),
                'visitor_email' => $request->input('email', auth()->user()->email ?? null),
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        $messages = $session->messages()
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        $response = response()->json([
            'session_id' => $session->id,
            'visitor_id' => $visitorId,
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_admin' => $m->is_admin,
                'sender' => $m->user ? $m->user->name : ($m->is_admin ? 'Support' : 'You'),
                'created_at' => $m->created_at->format('h:i A'),
            ]),
            'unread' => $session->unreadCount(),
        ]);

        $response->withCookie(cookie()->forever('chat_visitor_id', $visitorId));

        return $response;
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'visitor_id' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $visitorId = $request->visitor_id ?: $request->cookie('chat_visitor_id') ?: Str::uuid()->toString();

        $session = ChatSession::where('visitor_id', $visitorId)
            ->orWhere('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (!$session) {
            $session = ChatSession::create([
                'user_id' => auth()->id(),
                'visitor_id' => $visitorId,
                'visitor_name' => $request->name ?? auth()->user()->name ?? null,
                'visitor_email' => $request->email ?? auth()->user()->email ?? null,
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        $message = $session->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin' => false,
        ]);

        $session->update(['last_message_at' => now()]);

        return response()->json([
            'id' => $message->id,
            'message' => $message->message,
            'is_admin' => false,
            'sender' => auth()->user()->name ?? ($session->visitor_name ?? 'You'),
            'created_at' => $message->created_at->format('h:i A'),
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        $visitorId = $request->cookie('chat_visitor_id');
        if (!$visitorId) {
            return response()->json(['messages' => []]);
        }

        $sessionId = $request->input('session_id');
        $lastId = $request->input('last_id', 0);

        $query = ChatSession::where('visitor_id', $visitorId);
        if ($sessionId) {
            $query->where('id', $sessionId);
        }
        $session = $query->first();

        if (!$session) {
            return response()->json(['messages' => []]);
        }

        $newMessages = $session->messages()
            ->where('id', '>', $lastId)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $newMessages->map(fn($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_admin' => $m->is_admin,
                'sender' => $m->user ? $m->user->name : ($m->is_admin ? 'Support' : 'You'),
                'created_at' => $m->created_at->format('h:i A'),
            ]),
        ]);
    }
}
