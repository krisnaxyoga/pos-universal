<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $cashier;
    protected Category $category;

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
    }

    public function test_admin_can_view_products_list(): void
    {
        Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Product');
    }

    public function test_admin_can_create_product(): void
    {
        $productData = [
            'name' => 'New Product',
            'sku' => 'NEW-001',
            'price' => 25000,
            'cost' => 15000,
            'stock' => 50,
            'min_stock' => 5,
            'category_id' => $this->category->id,
            'description' => 'A new test product',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'sku' => 'NEW-001',
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'name' => 'Original Product',
            'sku' => 'ORIG-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $updatedData = [
            'name' => 'Updated Product',
            'sku' => 'ORIG-001',
            'price' => 15000,
            'stock' => 150,
            'min_stock' => 15,
            'category_id' => $this->category->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->put(route('products.update', $product), $updatedData);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 15000,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'name' => 'To Delete',
            'sku' => 'DEL-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_cashier_cannot_create_product(): void
    {
        $productData = [
            'name' => 'Cashier Product',
            'sku' => 'CASH-001',
            'price' => 25000,
            'stock' => 50,
            'min_stock' => 5,
            'category_id' => $this->category->id,
        ];

        $response = $this->actingAs($this->cashier)->post(route('products.store'), $productData);

        $response->assertStatus(403);
    }

    public function test_product_low_stock_detection(): void
    {
        $product = Product::create([
            'name' => 'Low Stock Product',
            'sku' => 'LOW-001',
            'price' => 10000,
            'stock' => 5,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertTrue($product->isLowStock());

        $product->update(['stock' => 20]);
        $product->refresh();

        $this->assertFalse($product->isLowStock());
    }

    public function test_product_active_scope(): void
    {
        Product::create([
            'name' => 'Active Product',
            'sku' => 'ACT-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Inactive Product',
            'sku' => 'INACT-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => false,
        ]);

        $activeProducts = Product::active()->get();

        $this->assertCount(1, $activeProducts);
        $this->assertEquals('Active Product', $activeProducts->first()->name);
    }

    public function test_product_low_stock_scope(): void
    {
        Product::create([
            'name' => 'Normal Stock',
            'sku' => 'NORM-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Low Stock',
            'sku' => 'LOW-001',
            'price' => 10000,
            'stock' => 5,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $lowStockProducts = Product::lowStock()->get();

        $this->assertCount(1, $lowStockProducts);
        $this->assertEquals('Low Stock', $lowStockProducts->first()->name);
    }

    public function test_product_belongs_to_category(): void
    {
        $product = Product::create([
            'name' => 'Category Test',
            'sku' => 'CAT-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals('Test Category', $product->category->name);
    }

    public function test_product_validation_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), []);

        $response->assertSessionHasErrors(['name', 'price', 'stock', 'category_id']);
    }

    public function test_product_sku_must_be_unique(): void
    {
        Product::create([
            'name' => 'First Product',
            'sku' => 'UNIQUE-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Second Product',
            'sku' => 'UNIQUE-001',
            'price' => 15000,
            'stock' => 50,
            'min_stock' => 5,
            'category_id' => $this->category->id,
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    public function test_guest_cannot_access_products(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }
}
