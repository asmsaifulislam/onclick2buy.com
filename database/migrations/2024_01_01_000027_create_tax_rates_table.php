<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tax_rates')) {
            Schema::create('tax_rates', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('VAT');
                $table->decimal('rate', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            if (\Illuminate\Support\Facades\DB::table('tax_rates')->count() === 0) {
                \Illuminate\Support\Facades\DB::table('tax_rates')->insert([
                    'name' => 'VAT',
                    'rate' => 0.00,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
