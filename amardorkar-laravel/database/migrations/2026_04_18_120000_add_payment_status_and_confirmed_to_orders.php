<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Add missing columns to orders table
        // NOTE: 'confirmed' was added to initial migration, no enum->change() needed here
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', ['pending','paid','failed','refunded'])->default('pending')->after('payment_method');
            $table->boolean('admin_notified')->default(false)->after('payment_status');
            $table->timestamp('admin_notification_time')->nullable()->after('admin_notified');
            $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete()->after('admin_notification_time');
        });

        // Add discount tracking to order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('original_price', 10, 2)->nullable()->after('price');
            $table->boolean('is_discounted')->default(false)->after('original_price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('is_discounted');
            $table->string('product_type')->nullable()->after('product_name');
            $table->string('product_size')->nullable()->after('product_type');
        });

        // Create sales_history table (completed/delivered orders archive)
        Schema::create('sales_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('product_type')->nullable();
            $table->string('product_size')->nullable();
            $table->text('product_details')->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_mobile');
            $table->text('customer_address');
            $table->string('shipping_location')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->boolean('is_discounted')->default(false);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('total_product_amount', 10, 2);
            $table->decimal('shipping_charge', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('final_amount', 10, 2);
            $table->boolean('discount_applied')->default(false);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('original_unit_price', 10, 2)->nullable();
            $table->string('order_status')->default('delivered');
            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('completed');
            $table->string('sold_by')->nullable();
            $table->string('delivery_status')->default('delivered');
            $table->text('notes')->nullable();
            $table->timestamp('order_time')->nullable();
            $table->timestamp('selling_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sales_history');
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['original_price','is_discounted','discount_amount','product_type','product_size']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status','admin_notified','admin_notification_time','assigned_admin_id']);
        });
    }
};
