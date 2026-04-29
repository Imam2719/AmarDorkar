<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // product_reviews table
        if (!Schema::hasTable('product_reviews')) {
            Schema::create('product_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->tinyInteger('rating')->default(5);
                $table->text('comment');
                $table->string('review_image')->nullable();
                $table->boolean('is_approved')->default(true);
                $table->timestamps();
            });
        }

        // is_new_arrival column products-এ
        if (!Schema::hasColumn('products', 'is_new_arrival')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_new_arrival')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('product_reviews');
        if (Schema::hasColumn('products', 'is_new_arrival')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_new_arrival');
            });
        }
    }
};