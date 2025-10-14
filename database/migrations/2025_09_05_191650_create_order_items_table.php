<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('product_name'); // Store product name at time of order
            $table->string('product_sku'); // Store product SKU at time of order
            $table->json('print_dimensions')->nullable(); // Width, height, position on product
            $table->boolean('is_gangsheet_item')->default(false);
            $table->unsignedBigInteger('gangsheet_id')->nullable();
            $table->json('design_specifications')->nullable();
            $table->json('dimensions')->nullable(); // Custom dimensions for this item
            $table->json('color_options')->nullable(); // Selected colors for this item
            $table->text('special_instructions')->nullable();
            $table->json('gang_sheet_position')->nullable(); // Position data if part of gang sheet
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('gangsheet_id')->references('id')->on('gangsheets')->onDelete('set null');

            $table->index(['order_id']);
            $table->index(['product_id']);
            $table->index(['gangsheet_id']);
            $table->index(['is_gangsheet_item']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
