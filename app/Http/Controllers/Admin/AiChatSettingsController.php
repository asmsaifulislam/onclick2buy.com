<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Services\AiAgentService;
use Illuminate\Http\Request;

class AiChatSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'provider' => StoreSetting::get('ai_provider', 'rasa'),
            'enabled' => StoreSetting::get('ai_chat_enabled', '1'),
            'external_api_url' => StoreSetting::get('ai_external_api_url', ''),
            'external_api_key' => StoreSetting::get('ai_external_api_key', ''),
            'external_model' => StoreSetting::get('ai_external_model', 'bytedance-seed/dola-seed-2.0-pro:free'),
            'external_system_prompt' => StoreSetting::get('ai_external_system_prompt', 'You are a helpful support assistant for our store. Answer only about products, shipping, hours, and policies. Keep replies short and friendly. If unsure, say "Please contact support."'),
            'handoff_enabled' => StoreSetting::get('ai_handoff_enabled', '1'),
            'handoff_keywords' => StoreSetting::get('ai_handoff_keywords', 'human,agent,person,support,help'),
        ];

        return view('admin.ai-chat-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->only([
            'provider', 'enabled',
            'external_api_url', 'external_api_key', 'external_model', 'external_system_prompt',
            'handoff_enabled', 'handoff_keywords',
        ]);

        foreach ($data as $key => $value) {
            if ($key === 'external_api_key' && empty($value)) {
                continue;
            }
            StoreSetting::set('ai_' . $key, $value);
        }

        return back()->with('success', 'AI Chatbot settings updated!');
    }

    public function test(Request $request)
    {
        $aiAgent = new AiAgentService();
        $result = $aiAgent->testConnection();
        return response()->json($result);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $aiAgent = new AiAgentService();
        $sessionId = $request->input('session_id', 'admin-test-' . uniqid());
        $result = $aiAgent->sendMessage($request->input('message'), $sessionId);

        return response()->json($result ?? [
            'success' => false,
            'reply' => 'No response from AI provider.',
        ]);
    }
}
