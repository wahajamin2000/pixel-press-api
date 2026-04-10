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
        Schema::create('gangsheet_products', function (Blueprint $table) {
            $table->id();

            // External API Reference
            $table->string('design_id')->unique()->comment('Gang sheet design ID from BuildAGangSheet');

            // Design Information
            $table->string('name')->nullable();
            $table->string('file_name')->nullable();
            $table->string('size')->nullable()->comment('Sheet size (e.g., 22x22, 16x20)');
            $table->string('order_type')->nullable()->comment('DTF, DTG, Sublimation, etc.');
            $table->string('quality')->nullable()->comment('Quality setting used');

            // Status and Processing
            $table->string('status')->default('pending');

            // File URLs and Downloads
            $table->text('download_url')->nullable()->comment('Direct download link');
            $table->text('thumbnail_url')->nullable()->comment('Preview/thumbnail image');
            $table->text('edit_url')->nullable()->comment('Link to edit in BuildAGangSheet');

            // Images/Designs included in gang sheet
            $table->json('images')->nullable()->comment('Array of images included in the gang sheet');

            // Additional Metadata
            $table->json('metadata')->nullable()->comment('Additional data from API response');
            $table->decimal('width', 8, 2)->nullable()->comment('Width in inches');
            $table->decimal('height', 8, 2)->nullable()->comment('Height in inches');
            $table->integer('image_count')->default(0)->comment('Number of images in gang sheet');

            // Processing Timestamps
            $table->timestamp('generated_at')->nullable()->comment('When the gang sheet was generated');
            $table->timestamp('last_synced_at')->nullable()->comment('Last time data was synced from API');

            // Standard Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('design_id');
            $table->index('status');
            $table->index(['status', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gangsheet_products');
    }
};
