<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\StoreSetting;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'store_name' => 'OnClick2Buy',
            'store_tagline' => 'Your premier destination for quality products at unbeatable prices.',
            'store_email' => 'support@onclick2buy.com',
            'store_phone' => '+1 (555) 123-4567',
            'store_address' => '123 Commerce St, Tech City, TC 10001',
            'store_currency' => 'BDT',
            'store_currency_symbol' => '৳',
            'social_facebook' => '#',
            'social_twitter' => '#',
            'social_instagram' => '#',
            'social_tiktok' => '#',
            'hero_title' => 'Premium Shopping Experience',
            'hero_subtitle' => 'Discover curated collections of high-quality products with unbeatable prices and fast delivery.',
            'hero_cta_text' => 'Shop Now',
            'footer_text' => 'Made with care.',
            'announcement_text' => '',
            'announcement_enabled' => '0',
            'meta_description' => 'OnClick2Buy - Your premier destination for quality products at unbeatable prices.',
        ];

        foreach ($defaults as $key => $value) {
            StoreSetting::create(['key' => $key, 'value' => $value]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
