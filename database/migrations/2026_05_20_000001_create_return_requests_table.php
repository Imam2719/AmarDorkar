<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('reason', [
                'defective_product',
                'wrong_item',
                'not_as_described',
                'damaged_in_shipping',
                'changed_mind',
                'missing_parts',
                'other',
            ])->default('other');
            $table->text('description');
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'refund_initiated',
                'refunded',
            ])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_method')->nullable(); // bkash, nagad, bank, original_payment
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'user_id']);
            $table->index('status');
        });

        // Up to 5 images per return request — stored as base64 in DB (same pattern as review images)
        Schema::create('return_request_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
            $table->longText('image_data');   // base64 encoded
            $table->string('mime_type', 50)->default('image/jpeg');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_images');
        Schema::dropIfExists('return_requests');
    }
};
