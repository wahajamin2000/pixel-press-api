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
        Schema::create('design_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->unsignedBigInteger('gangsheet_id')->nullable();
            $table->string('file_path', 500);
            $table->string('original_name');
            $table->string('file_name');
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->json('dimensions')->nullable(); // Width, height in pixels
            $table->unsignedInteger('dpi')->nullable();
            $table->boolean('has_transparent_background')->default(false);
            $table->enum('status', ['uploaded', 'processing', 'approved', 'rejected'])->default('uploaded');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('gangsheet_id')->references('id')->on('gangsheets')->onDelete('cascade');

            $table->index(['order_item_id']);
            $table->index(['gangsheet_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_files');
    }
};
