<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Search-এ LIKE '%...%' full table scan করে।
 * name_bn, name_en, description_bn, description_en, sku — সব কলামে prefix wildcard LIKE query।
 * MySQL-এ এটি সবসময় full table scan করে। ১০,০০০+ product হলে search অনেক slow হবে।
 *
 * Fix: MySQL FULLTEXT index যোগ করো।
 * SQLite (dev/test) এ FULLTEXT support নেই, তাই DB driver check করে skip করা হয়।
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL FULLTEXT index — Blueprint দিয়ে সরাসরি
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(
                    ['name_bn', 'name_en', 'description_bn', 'description_en', 'sku'],
                    'products_search_fulltext'
                );
            });
        }
        // SQLite/pgsql: FULLTEXT নেই, skip। SearchController-এ MATCH() fallback আছে।
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->dropFullText('products_search_fulltext');
            });
        }
    }
};
