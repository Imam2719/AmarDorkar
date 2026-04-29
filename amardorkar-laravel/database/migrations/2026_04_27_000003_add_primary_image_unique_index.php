<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * FIX: product_images-এ (product_id, is_primary=true)-এর উপর unique constraint নেই।
 * Code-এ update(['is_primary' => false]) করলেও race condition-এ একাধিক primary image
 * থাকতে পারে।
 *
 * MySQL partial unique index সরাসরি support করে না, তাই application-level
 * enforcement করা হচ্ছে:
 * 1. Existing duplicates cleanup (keep latest primary per product)
 * 2. ProductImage model-এ Observer দিয়ে enforce করা হবে (ProductImageObserver)
 *
 * SQLite/MySQL compatible approach: unique index on (product_id) filtered via Observer.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Clean up: per product, keep only one is_primary=true (the latest)
        $duplicates = DB::table('product_images')
            ->where('is_primary', true)
            ->select('product_id', DB::raw('MAX(id) as keep_id'))
            ->groupBy('product_id')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('product_images')
                ->where('product_id', $dup->product_id)
                ->where('is_primary', true)
                ->where('id', '!=', $dup->keep_id)
                ->update(['is_primary' => false]);
        }

        // Add index on (product_id, is_primary) for fast primary image lookups
        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'is_primary'], 'pi_product_is_primary_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('pi_product_is_primary_index');
        });
    }
};
