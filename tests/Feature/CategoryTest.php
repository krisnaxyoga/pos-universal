<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_admin_can_view_categories_list(): void
    {
        Category::create([
            'name' => 'Test Category',
            'description' => 'Test description',
        ]);

        $response = $this->actingAs($this->admin)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Category');
    }

    public function test_admin_can_create_category(): void
    {
        $categoryData = [
            'name' => 'New Category',
            'description' => 'A new category description',
        ];

        $response = $this->actingAs($this->admin)->post(route('categories.store'), $categoryData);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create([
            'name' => 'Original Category',
            'description' => 'Original description',
        ]);

        $updatedData = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->admin)->put(route('categories.update', $category), $updatedData);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    public function test_admin_can_delete_category_without_products(): void
    {
        $category = Category::create([
            'name' => 'To Delete',
            'description' => 'Will be deleted',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $category = Category::create([
            'name' => 'Has Products',
            'description' => 'This category has products',
        ]);

        Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 10000,
            'stock' => 100,
            'min_stock' => 10,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        // Category should still exist
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cashier_cannot_create_category(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('categories.store'), [
            'name' => 'Cashier Category',
        ]);

        $response->assertStatus(403);
    }

    public function test_category_has_many_products(): void
    {
        $category = Category::create([
            'name' => 'Category With Products',
            'description' => 'Has multiple products',
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

        $this->assertCount(2, $category->products);
    }

    public function test_category_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'description' => 'No name provided',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_guest_cannot_access_categories(): void
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_single_category(): void
    {
        $category = Category::create([
            'name' => 'View Category',
            'description' => 'Category to view',
        ]);

        $response = $this->actingAs($this->admin)->get(route('categories.show', $category));

        $response->assertStatus(200);
        $response->assertSee('View Category');
    }
}
