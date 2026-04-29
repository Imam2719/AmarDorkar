<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG FIX: order_items table-এ selected_size column যোগ করা হচ্ছে।
 *
 * আগের migration (2026_04_25_100000_add_selected_size_to_cart_items.php)
 * শুধু cart_items table-এ column add করেছিল।
 * কিন্তু OrderController::show() এবং order emails-এ $item->selected_size
 * access করা হচ্ছিল order_items থেকে — এই column সেখানে ছিল না।
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('selected_size')->nullable()->after('product_size');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('selected_size');
        });
    }
};
