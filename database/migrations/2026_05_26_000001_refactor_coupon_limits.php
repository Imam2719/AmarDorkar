<?php
// PATH: database/migrations/2026_05_26_000001_refactor_coupon_limits.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // নতুন দুটো flexible column
            $table->unsignedInteger('per_user_limit')->nullable()->after('usage_limit')
                  ->comment('null = unlimited per user');
            $table->unsignedInteger('global_limit')->nullable()->after('per_user_limit')
                  ->comment('null = unlimited globally');
        });

        // পুরনো use_type + usage_limit → নতুন column-এ migrate
        // single   → per_user_limit=1,  global_limit=null
        // multiple → per_user_limit=null, global_limit=null
        // custom   → per_user_limit=null, global_limit=usage_limit
        DB::statement("
            UPDATE coupons SET
                per_user_limit = CASE
                    WHEN use_type = 'single' THEN 1
                    ELSE NULL
                END,
                global_limit = CASE
                    WHEN use_type = 'custom' THEN usage_limit
                    ELSE NULL
                END
        ");

        Schema::table('coupons', function (Blueprint $table) {
            // পুরনো column সরাও
            $table->dropColumn(['use_type', 'usage_limit']);
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('use_type', ['single', 'multiple', 'custom'])
                  ->default('multiple')->after('used_count');
            $table->unsignedInteger('usage_limit')->nullable()->after('use_type');
        });

        // Reverse migrate
        DB::statement("
            UPDATE coupons SET
                use_type = CASE
                    WHEN per_user_limit = 1 THEN 'single'
                    WHEN global_limit IS NOT NULL THEN 'custom'
                    ELSE 'multiple'
                END,
                usage_limit = global_limit
        ");

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['per_user_limit', 'global_limit']);
        });
    }
};
