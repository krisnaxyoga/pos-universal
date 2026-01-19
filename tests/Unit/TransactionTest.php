<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);
    }

    public function test_transaction_number_is_auto_generated(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $this->assertNotNull($transaction->transaction_number);
        $this->assertStringStartsWith('TRX', $transaction->transaction_number);
    }

    public function test_transaction_is_draft(): void
    {
        $draft = Transaction::create([
            'transaction_number' => 'TRX-DRAFT',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'payment_method' => 'cash',
            'status' => 'draft',
            'is_draft' => true,
        ]);

        $this->assertTrue($draft->isDraft());
    }

    public function test_completed_transaction_is_not_draft(): void
    {
        $completed = Transaction::create([
            'transaction_number' => 'TRX-COMP',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $this->assertFalse($completed->isDraft());
    }

    public function test_cash_transaction_can_be_refunded(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-CASH',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $this->assertTrue($transaction->canBeRefunded());
    }

    public function test_online_transaction_cannot_be_refunded(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-ONLINE',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'payment_method' => 'online',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $this->assertFalse($transaction->canBeRefunded());
    }

    public function test_pending_transaction_cannot_be_refunded(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-PEND',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'payment_method' => 'cash',
            'status' => 'pending',
            'is_draft' => false,
        ]);

        $this->assertFalse($transaction->canBeRefunded());
    }

    public function test_transaction_is_refund(): void
    {
        $original = Transaction::create([
            'transaction_number' => 'TRX-ORIG',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $refund = Transaction::create([
            'transaction_number' => 'TRX-REF',
            'user_id' => $this->user->id,
            'subtotal' => -10000,
            'tax' => -1000,
            'total' => -11000,
            'payment_method' => 'cash',
            'status' => 'refunded',
            'is_draft' => false,
            'refund_reference_id' => $original->id,
            'refund_amount' => 11000,
            'refund_reason' => 'Customer request',
        ]);

        $this->assertTrue($refund->isRefund());
        $this->assertFalse($original->isRefund());
    }

    public function test_transaction_total_refunded_amount(): void
    {
        $original = Transaction::create([
            'transaction_number' => 'TRX-ORIG',
            'user_id' => $this->user->id,
            'subtotal' => 100000,
            'tax' => 10000,
            'total' => 110000,
            'paid' => 150000,
            'change' => 40000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-REF1',
            'user_id' => $this->user->id,
            'subtotal' => -50000,
            'tax' => -5000,
            'total' => -55000,
            'payment_method' => 'cash',
            'status' => 'refunded',
            'is_draft' => false,
            'refund_reference_id' => $original->id,
            'refund_amount' => 55000,
        ]);

        $this->assertEquals(55000, $original->getTotalRefundedAmount());
    }

    public function test_transaction_customer_info_is_cast_to_array(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-CUST',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
            'customer_info' => ['name' => 'John', 'phone' => '1234567890'],
        ]);

        $this->assertIsArray($transaction->customer_info);
        $this->assertEquals('John', $transaction->customer_info['name']);
    }

    public function test_transaction_is_draft_is_cast_to_boolean(): void
    {
        $draft = Transaction::create([
            'transaction_number' => 'TRX-BOOL',
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'payment_method' => 'cash',
            'status' => 'draft',
            'is_draft' => 1,
        ]);

        $this->assertIsBool($draft->is_draft);
        $this->assertTrue($draft->is_draft);
    }

    public function test_transaction_amounts_are_cast_to_decimal(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-DEC',
            'user_id' => $this->user->id,
            'subtotal' => 10000.50,
            'tax' => 1000.05,
            'total' => 11000.55,
            'paid' => 15000.00,
            'change' => 3999.45,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $this->assertIsString($transaction->subtotal);
        $this->assertIsString($transaction->tax);
        $this->assertIsString($transaction->total);
    }

    public function test_transaction_fillable_attributes(): void
    {
        $transaction = new Transaction();
        $fillable = $transaction->getFillable();

        $this->assertContains('transaction_number', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('customer_id', $fillable);
        $this->assertContains('subtotal', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('payment_method', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('is_draft', $fillable);
    }
}
