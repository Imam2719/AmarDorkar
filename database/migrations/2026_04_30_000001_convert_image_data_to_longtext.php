<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Convert image_data columns from LONGBLOB to LONGTEXT
 * so base64-encoded images store reliably via Eloquent.
 */
return new class extends Migration
{
    public function up(): void
    {
        // banners
        if (Schema::hasColumn('banners', 'image_data')) {
            DB::statement('ALTER TABLE banners MODIFY image_data LONGTEXT NULL');
        }

        // product_images
        if (Schema::hasColumn('product_images', 'image_data')) {
            DB::statement('ALTER TABLE product_images MODIFY image_data LONGTEXT NULL');
        }

        // product_reviews
        if (Schema::hasColumn('product_reviews', 'review_image_data')) {
            DB::statement('ALTER TABLE product_reviews MODIFY review_image_data LONGTEXT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('banners', 'image_data')) {
            DB::statement('ALTER TABLE banners MODIFY image_data LONGBLOB NULL');
        }
        if (Schema::hasColumn('product_images', 'image_data')) {
            DB::statement('ALTER TABLE product_images MODIFY image_data LONGBLOB NULL');
        }
        if (Schema::hasColumn('product_reviews', 'review_image_data')) {
            DB::statement('ALTER TABLE product_reviews MODIFY review_image_data LONGBLOB NULL');
        }
    }
};
