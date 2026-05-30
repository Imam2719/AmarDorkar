<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Exact timestamp যখন order 'delivered' status এ গেছে
            $table->timestamp('delivered_at')->nullable()->after('status');
        });

        // ── Backfill: পুরনো delivered orders এ delivered_at সেট করো ──
        // updated_at টা perfect না, কিন্তু migrate করার আগে আর কোনো data নেই
        // তাই existing delivered orders এর জন্য updated_at ব্যবহার করছি (one-time backfill)
        DB::statement("
            UPDATE orders
            SET delivered_at = updated_at
            WHERE status = 'delivered'
              AND delivered_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
};
