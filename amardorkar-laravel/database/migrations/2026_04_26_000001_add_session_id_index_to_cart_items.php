<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX PERF 5: Add database index on cart_items.session_id.
 *
 * Guest cart queries filter by session_id on every page load.
 * Without an index, MySQL does a full table scan each time.
 * This index makes those lookups O(log n) instead of O(n).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('session_id', 'cart_items_session_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_session_id_index');
        });
    }
};
