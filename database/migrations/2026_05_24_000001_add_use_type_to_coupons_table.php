<?php
// PATH: database/migrations/2026_05_24_000001_add_use_type_to_coupons_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // 'single' = ১ বার ব্যবহার, 'multiple' = যতবার খুশি, 'custom' = নির্দিষ্ট সংখ্যা
            $table->enum('use_type', ['single', 'multiple', 'custom'])
                  ->default('multiple')
                  ->after('usage_limit');

            // Coupon expire হলে auto history-তে যাবে, এই timestamp track করবে
            $table->timestamp('expired_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['use_type', 'expired_at']);
        });
    }
};
