<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable(); // cap for percentage coupons
            $table->integer('usage_limit')->nullable();                // null = unlimited
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coupon_id', 'user_id']);
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
        });

        // Add coupon columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete()->after('total_amount');
            $table->decimal('coupon_discount', 10, 2)->default(0)->after('coupon_id');
            $table->string('coupon_code', 10)->nullable()->after('coupon_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_id', 'coupon_discount', 'coupon_code']);
        });
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupon_users');
        Schema::dropIfExists('coupons');
    }
};
