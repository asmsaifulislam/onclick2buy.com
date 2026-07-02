<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::getAll();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_tagline' => 'nullable|string|max:500',
            'store_email' => 'nullable|email|max:255',
            'store_phone' => 'nullable|string|max:50',
            'store_address' => 'nullable|string|max:500',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_cta_text' => 'nullable|string|max:100',
            'footer_text' => 'nullable|string|max:500',
            'announcement_text' => 'nullable|string|max:500',
            'announcement_enabled' => 'nullable|string',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $fields = [
            'store_name', 'store_tagline', 'store_email', 'store_phone', 'store_address',
            'store_currency', 'store_currency_symbol',
            'social_facebook', 'social_twitter', 'social_instagram', 'social_tiktok',
            'hero_title', 'hero_subtitle', 'hero_cta_text',
            'footer_text', 'announcement_text', 'announcement_enabled', 'meta_description',
        ];

        $data = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field, '');
            }
        }

        StoreSetting::setMany($data);

        return back()->with('success', 'Store settings updated successfully!');
    }
}
