<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Store binary image data directly in DB
            $table->mediumText('image_data')->nullable()->after('image_path'); // base64 encoded
            $table->string('mime_type', 50)->nullable()->after('image_data');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'mime_type']);
        });
    }
};
