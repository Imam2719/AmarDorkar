<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the legacy base64 `image_data` columns from product_images and banners.
 *
 * Background: images are now stored on disk via Laravel Storage (image_path).
 * The image_data (mediumText base64) columns were kept for "backward compat" but
 * they bloat the database significantly and serve no current purpose.
 *
 * Before running this migration make sure all images have been migrated
 * to file storage (image_path is populated).
 *
 * The ImageController (/img/product/{id} and /img/banner/{id}) routes that
 * read image_data will 404 gracefully after this, since image_data will be null.
 * Those legacy routes should be removed from web.php once all views use image_path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'mime_type']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'mime_type']);
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->mediumText('image_data')->nullable()->after('image_path');
            $table->string('mime_type', 50)->nullable()->after('image_data');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->mediumText('image_data')->nullable()->after('image_path');
            $table->string('mime_type', 50)->nullable()->after('image_data');
        });
    }
};
