<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title_en')->default('Flash Sale');
            $table->string('title_bn')->default('ফ্ল্যাশ সেল');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('flash_price', 10, 2);
            $table->integer('max_quantity')->default(50);
            $table->integer('sold_quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('flash_sale_products');
        Schema::dropIfExists('flash_sales');
    }
};
