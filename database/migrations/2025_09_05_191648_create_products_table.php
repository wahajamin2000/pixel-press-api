<?php

use App\Enums\ProductStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 50)->unique()->index();
            $table->text('description')->nullable()->default(null);
            $table->string('short_description', 500)->nullable()->default(null);
            $table->string('sku', 100)->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('base_price', 10, 2);
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable(); // {length, width, height}
            $table->string('material')->nullable();
            $table->json('color_options')->nullable();
            $table->json('size_options')->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->default(null);
            $table->string('status')->default(ProductStatus::DRAFT);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable()->default(null);
            $table->text('meta_description')->nullable()->default(null);
            $table->integer('sort_order')->default(0);
            $table->json('specifications')->nullable()->default(null); // For DTF-specific specs like material, sizes, etc.
            $table->json('print_areas')->nullable()->default(null); // Available print areas and dimensions
            $table->unsignedBigInteger('created_by')->nullable()->default(null);
            $table->unsignedBigInteger('updated_by')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['status', 'is_featured']);
            $table->index('category_id');
            $table->index('sort_order');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
