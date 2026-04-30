<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── products: drop legacy image_path ──────────────────────────
        if (Schema::hasColumn('products', 'image_path')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }

        // ── product_images: rebuild to DB-only ────────────────────────
        Schema::table('product_images', function (Blueprint $table) {
            if (Schema::hasColumn('product_images', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('product_images', 'image_data')) {
                $table->dropColumn('image_data');
            }
            if (Schema::hasColumn('product_images', 'mime_type')) {
                $table->dropColumn('mime_type');
            }
        });
        DB::statement('ALTER TABLE product_images ADD COLUMN image_data LONGTEXT NULL AFTER product_id');
        DB::statement('ALTER TABLE product_images ADD COLUMN mime_type VARCHAR(50) NULL AFTER image_data');

        // ── banners: rebuild to DB-only ───────────────────────────────
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('banners', 'image_data')) {
                $table->dropColumn('image_data');
            }
            if (Schema::hasColumn('banners', 'mime_type')) {
                $table->dropColumn('mime_type');
            }
        });
        DB::statement('ALTER TABLE banners ADD COLUMN image_data LONGTEXT NULL AFTER button_link');
        DB::statement('ALTER TABLE banners ADD COLUMN mime_type VARCHAR(50) NULL AFTER image_data');

        // ── product_reviews: drop file path, add binary columns ───────
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'review_image')) {
                $table->dropColumn('review_image');
            }
            if (Schema::hasColumn('product_reviews', 'review_image_data')) {
                $table->dropColumn('review_image_data');
            }
            if (Schema::hasColumn('product_reviews', 'review_mime_type')) {
                $table->dropColumn('review_mime_type');
            }
        });
        DB::statement('ALTER TABLE product_reviews ADD COLUMN review_image_data LONGTEXT NULL AFTER comment');
        DB::statement('ALTER TABLE product_reviews ADD COLUMN review_mime_type VARCHAR(50) NULL AFTER review_image_data');
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'image_path')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('image_path')->nullable();
            });
        }

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'mime_type']);
            $table->string('image_path')->nullable()->after('product_id');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'mime_type']);
            $table->string('image_path')->nullable()->after('button_link');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn(['review_image_data', 'review_mime_type']);
            $table->string('review_image')->nullable()->after('comment');
        });
    }
};