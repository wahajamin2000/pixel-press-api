<?php

use App\Enums\GangSheetStatus;
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
        Schema::create('gangsheets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('guest_session_id')->nullable();
            $table->json('dimensions'); // Width, height of the gangsheet
            $table->json('layout_data'); // Positions and arrangements of designs
            $table->unsignedInteger('total_designs')->default(0);
            $table->string('status')->default(GangSheetStatus::DRAFT);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('original_price', 10, 2)->default(0);
            $table->decimal('discounted_price', 10, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['user_id', 'status']);
            $table->index(['guest_session_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gangsheets');
    }
};
