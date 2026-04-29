<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Flash sale cancel restore — store which flash sale product was used at order time.
 * Without this, cancelling an order after its flash sale expires can't restore quota.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('flash_sale_product_id')
                  ->nullable()
                  ->after('product_size');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('flash_sale_product_id');
        });
    }
};
