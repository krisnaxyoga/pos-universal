<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    public function test_admin_can_view_customers_list(): void
    {
        Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->admin)->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_admin_can_create_customer(): void
    {
        $customerData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '081234567891',
            'address' => '123 Main Street',
        ];

        $response = $this->actingAs($this->admin)->post(route('customers.store'), $customerData);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_admin_can_update_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone' => '081234567890',
        ]);

        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '081234567891',
            'address' => 'Updated Address',
        ];

        $response = $this->actingAs($this->admin)->put(route('customers.update', $customer), $updatedData);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_admin_can_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'To Delete',
            'email' => 'delete@example.com',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_cashier_cannot_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Protected Customer',
            'email' => 'protected@example.com',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->cashier)->delete(route('customers.destroy', $customer));

        $response->assertStatus(403);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'email' => 'test@example.com',
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_customer_email_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'name' => 'Test Customer',
            'email' => 'invalid-email',
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_guest_cannot_access_customers(): void
    {
        $response = $this->get(route('customers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_single_customer(): void
    {
        $customer = Customer::create([
            'name' => 'View Customer',
            'email' => 'view@example.com',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ]);

        $response = $this->actingAs($this->admin)->get(route('customers.show', $customer));

        $response->assertStatus(200);
        $response->assertSee('View Customer');
        $response->assertSee('view@example.com');
    }

    public function test_customer_email_must_be_unique(): void
    {
        Customer::create([
            'name' => 'First Customer',
            'email' => 'unique@example.com',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'name' => 'Second Customer',
            'email' => 'unique@example.com',
            'phone' => '081234567891',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_search_customers(): void
    {
        Customer::create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'phone' => '081234567890',
        ]);

        Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '081234567891',
        ]);

        $response = $this->actingAs($this->admin)->get(route('customers.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertSee('John Smith');
    }
}
