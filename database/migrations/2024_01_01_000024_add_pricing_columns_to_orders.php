<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'discount_code')) {
                $table->string('discount_code')->nullable()->after('discount');
            }
            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0)->after('discount_code');
            }
            if (!Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('shipping_cost');
            }
            if (!Schema::hasColumn('orders', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->after('shipping_method');
            }
            if (!Schema::hasColumn('orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('tax');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = array_filter(
                ['subtotal', 'discount', 'discount_code', 'shipping_cost', 'shipping_method', 'tax', 'tax_rate'],
                fn($c) => Schema::hasColumn('orders', $c)
            );
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
