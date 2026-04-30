<?php

use Illuminate\Database\Migrations\Migration;

/**
 * This migration is now a no-op.
 * All image storage conversion is handled by
 * 2026_04_29_000001_convert_all_images_to_db_storage.php
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: superseded by 2026_04_29_000001_convert_all_images_to_db_storage
    }

    public function down(): void
    {
        // No-op
    }
};
