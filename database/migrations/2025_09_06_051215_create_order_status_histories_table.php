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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->string('from_status')->default(null)->nullable();
            $table->string('to_status')->default(null)->nullable();
            $table->timestamp('changed_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'changed_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
