<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Services\FacebookService;
use Illuminate\Http\Request;

class FacebookController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::getAll();
        $facebook = new FacebookService();
        $isConfigured = $facebook->isConfigured();

        $connectionStatus = null;
        if ($isConfigured) {
            $connectionStatus = $facebook->testConnection();
        }

        return view('admin.facebook.index', compact('settings', 'isConfigured', 'connectionStatus'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'fb_page_id' => 'required|string|max:100',
            'fb_page_access_token' => 'required|string|max:1000',
        ]);

        StoreSetting::set('fb_page_id', $request->input('fb_page_id'));
        StoreSetting::set('fb_page_access_token', $request->input('fb_page_access_token'));

        return back()->with('success', 'Facebook settings updated successfully!');
    }

    public function testConnection()
    {
        $facebook = new FacebookService();
        $result = $facebook->testConnection();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'page_name' => $result['page_name'] ?? '',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Connection failed',
        ]);
    }

    public function recentPosts()
    {
        $facebook = new FacebookService();
        if (!$facebook->isConfigured()) {
            return response()->json(['success' => false, 'error' => 'Facebook is not configured.']);
        }

        try {
            $pageId = StoreSetting::get('fb_page_id', '');
            $token = StoreSetting::get('fb_page_access_token', '');
            $response = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v19.0/{$pageId}/feed", [
                'fields' => 'id,message,created_time,full_picture,permalink_url',
                'limit' => 10,
                'access_token' => $token,
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'data' => $response->json('data', [])]);
            }

            return response()->json(['success' => false, 'error' => $response->json('error.message', 'Failed to fetch posts')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
