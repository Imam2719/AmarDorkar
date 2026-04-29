<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: sales_history table-এ redundant columns আছে।
 *
 * সমস্যা:
 *   - is_discounted এবং discount_applied — দুটোই boolean, একই মানে বোঝায়
 *   - total_amount, final_amount, total_product_amount, subtotal — overlap আছে
 *
 * Fix:
 *   - is_discounted → canonical হিসেবে রাখো
 *   - discount_applied → is_discounted থেকে data migrate করে drop করো
 *   - total_product_amount → subtotal-এর same data, drop করো
 *   - total_amount → final_amount-এর সাথে same (shipping included), drop করো
 *
 * NOTE: আগে data migrate করা হয়, তারপর column drop।
 * SalesHistory model-এর fillable এবং casts আপডেট করা হয়েছে।
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: discount_applied → is_discounted sync (যেখানে mismatch)
        DB::statement('UPDATE sales_history SET is_discounted = discount_applied WHERE is_discounted != discount_applied');

        // Step 2: total_product_amount → subtotal sync
        DB::statement('UPDATE sales_history SET subtotal = total_product_amount WHERE subtotal = 0 OR subtotal IS NULL');

        // Step 3: Drop redundant columns
        Schema::table('sales_history', function (Blueprint $table) {
            $table->dropColumn(['discount_applied', 'total_product_amount', 'total_amount']);
        });
    }

    public function down(): void
    {
        // Restore dropped columns
        Schema::table('sales_history', function (Blueprint $table) {
            $table->boolean('discount_applied')->default(false)->after('is_discounted');
            $table->decimal('total_product_amount', 10, 2)->after('subtotal');
            $table->decimal('total_amount', 10, 2)->after('shipping_charge');
        });

        // Restore data from canonical columns
        DB::statement('UPDATE sales_history SET discount_applied = is_discounted');
        DB::statement('UPDATE sales_history SET total_product_amount = subtotal');
        DB::statement('UPDATE sales_history SET total_amount = final_amount');
    }
};
