<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * FIX: flash_sale_products-এ (flash_sale_id, product_id) unique constraint নেই।
 * একই product একটি flash sale-এ একাধিকবার add করা সম্ভব।
 * Controller-এ check নেই, migration-এও unique constraint নেই।
 * এতে duplicate flash sale product entries হতে পারে।
 */
return new class extends Migration
{
    public function up(): void
    {
        // Clean up any existing duplicates — keep the one with highest id
        DB::statement("
            DELETE FROM flash_sale_products
            WHERE id NOT IN (
                SELECT MAX(id)
                FROM flash_sale_products
                GROUP BY flash_sale_id, product_id
            )
        ");

        Schema::table('flash_sale_products', function (Blueprint $table) {
            $table->unique(
                ['flash_sale_id', 'product_id'],
                'fsp_flash_sale_product_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('flash_sale_products', function (Blueprint $table) {
            $table->dropUnique('fsp_flash_sale_product_unique');
        });
    }
};
