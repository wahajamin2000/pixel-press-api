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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('pickup_approval_status', [
                'none', 'pending', 'approved', 'rejected'
            ])->default('none')->after('status');

            $table->timestamp('pickup_approval_requested_at')->nullable()->after('pickup_approval_status');
            $table->timestamp('pickup_approval_reviewed_at')->nullable()->after('pickup_approval_requested_at');
            $table->unsignedBigInteger('pickup_approval_reviewed_by')->nullable()->after('pickup_approval_reviewed_at');
            $table->foreign('pickup_approval_reviewed_by')->references('id')->on('users')->nullOnDelete();

            $table->enum('tax_exempt_status', [
                'none', 'pending', 'approved', 'rejected',
            ])->default('none')->after('status');

            $table->string('tax_exempt_document')->nullable()->after('tax_exempt_status');
            $table->timestamp('tax_exempt_applied_at')->nullable()->after('tax_exempt_document');
            $table->timestamp('tax_exempt_reviewed_at')->nullable()->after('tax_exempt_applied_at');
            $table->unsignedBigInteger('tax_exempt_reviewed_by')->nullable()->after('tax_exempt_reviewed_at');
            $table->text('tax_exempt_rejection_reason')->nullable()->after('tax_exempt_reviewed_by');
            $table->foreign('tax_exempt_reviewed_by')->references('id')->on('users')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pickup_approval_reviewed_by']);
            $table->dropColumn([
                'pickup_approval_status',
                'pickup_approval_requested_at',
                'pickup_approval_reviewed_at',
                'pickup_approval_reviewed_by',
            ]);

            $table->dropForeign(['tax_exempt_reviewed_by']);
            $table->dropColumn([
                'tax_exempt_status', 'tax_exempt_document',
                'tax_exempt_applied_at', 'tax_exempt_reviewed_at',
                'tax_exempt_reviewed_by', 'tax_exempt_rejection_reason',
            ]);

        });
    }

};
