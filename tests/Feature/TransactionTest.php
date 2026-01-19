<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $cashier;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test category description',
        ]);

        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 10000,
            'cost' => 5000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_transactions_list(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX001',
            'user_id' => $this->cashier->id,
            'subtotal' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'paid' => 25000,
            'change' => 3000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('TRX001');
    }

    public function test_cashier_can_create_transaction(): void
    {
        $transactionData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ]
            ],
            'subtotal' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'paid' => 25000,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.transaction'), $transactionData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->cashier->id,
            'status' => 'completed',
        ]);

        // Check stock was reduced
        $this->product->refresh();
        $this->assertEquals(98, $this->product->stock);
    }

    public function test_transaction_reduces_product_stock(): void
    {
        $initialStock = $this->product->stock;

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.transaction'), [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                    ]
                ],
                'subtotal' => 50000,
                'tax' => 5000,
                'total' => 55000,
                'paid' => 55000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(200);

        $this->product->refresh();
        $this->assertEquals($initialStock - 5, $this->product->stock);
    }

    public function test_cannot_create_transaction_with_insufficient_stock(): void
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.transaction'), [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 999, // More than available stock
                    ]
                ],
                'subtotal' => 9990000,
                'tax' => 999000,
                'total' => 10989000,
                'paid' => 11000000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    public function test_transaction_number_is_auto_generated(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->cashier->id,
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

    public function test_transaction_completed_scope(): void
    {
        Transaction::create([
            'transaction_number' => 'TRX-COMP',
            'user_id' => $this->cashier->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-PEND',
            'user_id' => $this->cashier->id,
            'subtotal' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'payment_method' => 'online',
            'status' => 'pending',
            'is_draft' => false,
        ]);

        $completedTransactions = Transaction::completed()->get();

        $this->assertCount(1, $completedTransactions);
        $this->assertEquals('TRX-COMP', $completedTransactions->first()->transaction_number);
    }

    public function test_transaction_today_scope(): void
    {
        // Today's transaction
        Transaction::create([
            'transaction_number' => 'TRX-TODAY',
            'user_id' => $this->cashier->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
            'created_at' => now(),
        ]);

        $todayTransactions = Transaction::today()->get();

        $this->assertCount(1, $todayTransactions);
        $this->assertEquals('TRX-TODAY', $todayTransactions->first()->transaction_number);
    }

    public function test_can_save_draft_transaction(): void
    {
        $draftData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ]
            ],
            'subtotal' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'draft_name' => 'Test Draft',
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.draft.save'), $draftData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'draft_name' => 'Test Draft',
            'is_draft' => true,
            'status' => 'draft',
        ]);
    }

    public function test_draft_transaction_detection(): void
    {
        $draft = Transaction::create([
            'transaction_number' => 'TRX-DRAFT',
            'user_id' => $this->cashier->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'payment_method' => 'cash',
            'status' => 'draft',
            'is_draft' => true,
            'draft_name' => 'My Draft',
        ]);

        $this->assertTrue($draft->isDraft());

        $completed = Transaction::create([
            'transaction_number' => 'TRX-COMP',
            'user_id' => $this->cashier->id,
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

    public function test_transaction_can_be_refunded(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-REFUND',
            'user_id' => $this->cashier->id,
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

        // Online transactions cannot be refunded
        $onlineTransaction = Transaction::create([
            'transaction_number' => 'TRX-ONLINE',
            'user_id' => $this->cashier->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'payment_method' => 'online',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $this->assertFalse($onlineTransaction->canBeRefunded());
    }

    public function test_transaction_belongs_to_user(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-USER',
            'user_id' => $this->cashier->id,
            'subtotal' => 10000,
            'tax' => 1000,
            'total' => 11000,
            'paid' => 15000,
            'change' => 4000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertEquals($this->cashier->id, $transaction->user->id);
    }

    public function test_transaction_has_many_items(): void
    {
        $transaction = Transaction::create([
            'transaction_number' => 'TRX-ITEMS',
            'user_id' => $this->cashier->id,
            'subtotal' => 20000,
            'tax' => 2000,
            'total' => 22000,
            'paid' => 25000,
            'change' => 3000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_price' => $this->product->price,
            'quantity' => 2,
            'subtotal' => 20000,
        ]);

        $this->assertCount(1, $transaction->items);
        $this->assertEquals($this->product->name, $transaction->items->first()->product_name);
    }

    public function test_guest_cannot_access_pos(): void
    {
        $response = $this->get(route('pos.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_create_transaction(): void
    {
        $response = $this->postJson(route('pos.transaction'), [
            'items' => [],
            'total' => 0,
        ]);

        $response->assertStatus(401);
    }
}
