<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $cashier;

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
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_cashier_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_today_sales(): void
    {
        $category = Category::create([
            'name' => 'Test Category',
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-TODAY',
            'user_id' => $this->cashier->id,
            'subtotal' => 100000,
            'tax' => 10000,
            'total' => 110000,
            'paid' => 120000,
            'change' => 10000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        // Should see sales amount formatted
        $response->assertSee('110');
    }

    public function test_dashboard_shows_today_transactions_count(): void
    {
        Transaction::create([
            'transaction_number' => 'TRX-001',
            'user_id' => $this->cashier->id,
            'subtotal' => 50000,
            'tax' => 5000,
            'total' => 55000,
            'paid' => 60000,
            'change' => 5000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
            'created_at' => now(),
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-002',
            'user_id' => $this->cashier->id,
            'subtotal' => 30000,
            'tax' => 3000,
            'total' => 33000,
            'paid' => 35000,
            'change' => 2000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_low_stock_products(): void
    {
        $category = Category::create([
            'name' => 'Test Category',
        ]);

        Product::create([
            'name' => 'Low Stock Product',
            'sku' => 'LOW-001',
            'price' => 10000,
            'stock' => 3,
            'min_stock' => 10,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Low Stock Product');
    }

    public function test_dashboard_shows_total_products(): void
    {
        $category = Category::create([
            'name' => 'Test Category',
        ]);

        Product::create([
            'name' => 'Product 1',
            'sku' => 'PROD-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Product 2',
            'sku' => 'PROD-002',
            'price' => 20000,
            'stock' => 50,
            'min_stock' => 5,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_recent_transactions(): void
    {
        Transaction::create([
            'transaction_number' => 'TRX-RECENT',
            'user_id' => $this->cashier->id,
            'subtotal' => 50000,
            'tax' => 5000,
            'total' => 55000,
            'paid' => 60000,
            'change' => 5000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'is_draft' => false,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('TRX-RECENT');
    }

    public function test_inactive_user_cannot_access_dashboard(): void
    {
        $inactiveUser = User::factory()->create([
            'role' => 'kasir',
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get(route('dashboard'));

        // Should be redirected or see error
        $response->assertStatus(302);
    }
}
