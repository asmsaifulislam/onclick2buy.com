@extends('layouts.admin')
@section('title', 'Store Settings')
@section('content')
<div class="animate-fade-in-up">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Store Settings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Customize your storefront appearance and information</p>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-1">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">General Info</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Store Name</label>
                    <input type="text" name="store_name" value="{{ $settings['store_name'] ?? '' }}" class="input-field" required>
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Tagline</label>
                    <input type="text" name="store_tagline" value="{{ $settings['store_tagline'] ?? '' }}" class="input-field" placeholder="Your store tagline...">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="store_email" value="{{ $settings['store_email'] ?? '' }}" class="input-field">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" class="input-field">
                    </div>
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Address</label>
                    <input type="text" name="store_address" value="{{ $settings['store_address'] ?? '' }}" class="input-field">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700">Currency</label>
                        <select name="store_currency" class="input-field">
                            @foreach(['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'BDT' => 'BDT (৳)'] as $val => $lbl)
                                <option value="{{ $val }}" {{ ($settings['store_currency'] ?? 'USD') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700">Symbol</label>
                        <input type="text" name="store_currency_symbol" value="{{ $settings['store_currency_symbol'] ?? '$' }}" class="input-field" maxlength="3">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Hero Section (Homepage)</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Hero Title</label>
                    <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? '' }}" class="input-field">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Hero Subtitle</label>
                    <textarea name="hero_subtitle" rows="2" class="input-field">{{ $settings['hero_subtitle'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">CTA Button Text</label>
                    <input type="text" name="hero_cta_text" value="{{ $settings['hero_cta_text'] ?? 'Shop Now' }}" class="input-field">
                </div>
            </div>

            <div class="flex items-center gap-3 mb-5 mt-6 pt-5 border-t">
                <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Announcement Bar</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Announcement Text</label>
                    <input type="text" name="announcement_text" value="{{ $settings['announcement_text'] ?? '' }}" class="input-field" placeholder="e.g. Free shipping on orders over $50!">
                </div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="announcement_enabled" value="0">
                    <input type="checkbox" name="announcement_enabled" value="1" {{ ($settings['announcement_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">Enable announcement bar</span>
                </label>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Social Media Links</h2>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Facebook URL</label>
                    <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '#' }}" class="input-field" placeholder="https://facebook.com/...">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Twitter / X URL</label>
                    <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '#' }}" class="input-field" placeholder="https://twitter.com/...">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Instagram URL</label>
                    <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '#' }}" class="input-field" placeholder="https://instagram.com/...">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">TikTok URL</label>
                    <input type="url" name="social_tiktok" value="{{ $settings['social_tiktok'] ?? '#' }}" class="input-field" placeholder="https://tiktok.com/...">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Footer & SEO</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Footer Text</label>
                    <input type="text" name="footer_text" value="{{ $settings['footer_text'] ?? '' }}" class="input-field" placeholder="Made with care.">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">Meta Description (SEO)</label>
                    <textarea name="meta_description" rows="3" class="input-field" placeholder="Description for search engines...">{{ $settings['meta_description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end animate-fade-in-up animate-delay-4">
        <button type="submit" class="btn-primary inline-flex items-center gap-2 px-8 py-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Save All Settings
        </button>
    </div>
</form>
@endsection
