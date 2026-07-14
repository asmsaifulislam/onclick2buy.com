<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shipping_methods')) {
            Schema::create('shipping_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('cost', 12, 2)->default(0);
                $table->decimal('free_over', 12, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            if (\Illuminate\Support\Facades\DB::table('shipping_methods')->count() === 0) {
                \Illuminate\Support\Facades\DB::table('shipping_methods')->insert([
                    'name' => 'Standard Delivery',
                    'cost' => 60.00,
                    'free_over' => 500.00,
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
