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
            // iPaymu transaction fields
            $table->string('ipaymu_transaction_id')->nullable()->after('payment_method');
            $table->string('ipaymu_session_id')->nullable()->after('ipaymu_transaction_id');
            $table->string('ipaymu_reference_id')->nullable()->after('ipaymu_session_id');
            $table->decimal('ipaymu_amount', 15, 2)->nullable()->after('ipaymu_reference_id');
            $table->decimal('ipaymu_fee', 15, 2)->default(0)->after('ipaymu_amount');
            
            // Payment method details
            $table->string('ipaymu_payment_method')->nullable()->after('ipaymu_fee');
            $table->string('ipaymu_payment_channel')->nullable()->after('ipaymu_payment_method');
            $table->string('ipaymu_payment_code')->nullable()->after('ipaymu_payment_channel');
            $table->text('ipaymu_payment_url')->nullable()->after('ipaymu_payment_code');
            
            // QR and additional data
            $table->text('ipaymu_qr_string')->nullable()->after('ipaymu_payment_url');
            $table->timestamp('ipaymu_expired_date')->nullable()->after('ipaymu_qr_string');
            
            // Status tracking
            $table->string('ipaymu_status')->nullable()->after('ipaymu_expired_date');
            $table->string('ipaymu_status_code')->nullable()->after('ipaymu_status');
            $table->timestamp('ipaymu_paid_at')->nullable()->after('ipaymu_status_code');
            
            // Add indexes for better performance
            $table->index('ipaymu_transaction_id');
            $table->index('ipaymu_reference_id');
            $table->index('ipaymu_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['ipaymu_transaction_id']);
            $table->dropIndex(['ipaymu_reference_id']);
            $table->dropIndex(['ipaymu_status']);
            
            $table->dropColumn([
                'ipaymu_transaction_id',
                'ipaymu_session_id',
                'ipaymu_reference_id',
                'ipaymu_amount',
                'ipaymu_fee',
                'ipaymu_payment_method',
                'ipaymu_payment_channel',
                'ipaymu_payment_code',
                'ipaymu_payment_url',
                'ipaymu_qr_string',
                'ipaymu_expired_date',
                'ipaymu_status',
                'ipaymu_status_code',
                'ipaymu_paid_at',
            ]);
        });
    }
};