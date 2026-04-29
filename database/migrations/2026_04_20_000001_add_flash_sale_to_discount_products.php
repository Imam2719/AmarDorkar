<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('discount_products', function (Blueprint $table) {
            $table->boolean('is_flash_sale')->default(false)->after('is_active');
            $table->integer('flash_sale_quantity')->nullable()->after('is_flash_sale');
            $table->integer('flash_sale_sold')->default(0)->after('flash_sale_quantity');
        });
    }

    public function down(): void {
        Schema::table('discount_products', function (Blueprint $table) {
            $table->dropColumn(['is_flash_sale', 'flash_sale_quantity', 'flash_sale_sold']);
        });
    }
};
