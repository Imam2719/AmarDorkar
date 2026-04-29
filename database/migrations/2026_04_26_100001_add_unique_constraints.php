<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * FIX DB:
 * 1. discount_products: no unique on product_id => multiple active discounts possible
 *    Fix: unique index on product_id (only one discount record per product at a time)
 * 2. product_reviews: no unique (user_id, product_id) => race condition duplicates
 *    Fix: unique composite index
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Clean up any duplicate discount rows before adding constraint
        //    Keep the most recent active one per product
        DB::statement("
            DELETE FROM discount_products
            WHERE id NOT IN (
                SELECT MAX(id) FROM discount_products GROUP BY product_id
            )
        ");

        Schema::table('discount_products', function (Blueprint $table) {
            $table->unique('product_id', 'discount_products_product_id_unique');
        });

        // 2. Clean up duplicate reviews before adding constraint
        DB::statement("
            DELETE FROM product_reviews
            WHERE id NOT IN (
                SELECT MIN(id) FROM product_reviews GROUP BY user_id, product_id
            )
        ");

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id'], 'product_reviews_user_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('discount_products', function (Blueprint $table) {
            $table->dropUnique('discount_products_product_id_unique');
        });
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropUnique('product_reviews_user_product_unique');
        });
    }
};
