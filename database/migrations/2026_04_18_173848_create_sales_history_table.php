<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * FIX 5: Migration conflict resolved.
 *
 * The sales_history table is fully created by:
 *   2026_04_18_120000_add_payment_status_and_confirmed_to_orders.php
 *
 * This file previously duplicated that creation, causing a
 * "Table 'sales_history' already exists" error on fresh migrations.
 *
 * Resolution: This migration is now a no-op stub. The 120000 migration
 * remains the single source of truth for the sales_history schema.
 * Do NOT add table creation logic here.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: sales_history is created by the 120000 migration.
    }

    public function down(): void
    {
        // No-op: the 120000 migration's down() handles the drop.
    }
};
