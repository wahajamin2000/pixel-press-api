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
        Schema::create('order_item_design_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('file_type');
            $table->bigInteger('file_size'); // in bytes
            $table->json('dimensions')->nullable(); // {width, height} in pixels
            $table->json('resolution')->nullable(); // {dpi, ppi}
            $table->string('color_mode')->nullable(); // RGB, CMYK, etc.
            $table->timestamps();

            $table->index('order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_design_files');
    }
};
