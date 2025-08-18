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
        Schema::table('transactions', function (Blueprint $table) {
            // Add draft status to existing enum
            $table->enum('status', ['draft', 'pending', 'processing', 'completed', 'cancelled', 'failed', 'refunded'])->default('pending')->change();
            
            // Add fields for draft functionality
            $table->string('draft_name')->nullable()->after('notes');
            $table->boolean('is_draft')->default(false)->after('draft_name');
            
            // Add fields for refund functionality
            $table->foreignId('refund_reference_id')->nullable()->constrained('transactions')->onDelete('set null')->after('is_draft');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refund_reference_id');
            $table->text('refund_reason')->nullable()->after('refund_amount');
            $table->timestamp('refunded_at')->nullable()->after('refund_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['draft_name', 'is_draft', 'refund_reference_id', 'refund_amount', 'refund_reason', 'refunded_at']);
            
            // Revert status enum to original
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'failed'])->default('pending')->change();
        });
    }
};
