<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $supervisor;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->supervisor = User::factory()->create([
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee($this->cashier->name);
    }

    public function test_admin_can_create_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kasir',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'kasir',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $updatedData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'supervisor',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), $updatedData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'supervisor',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));

        // Admin should still exist
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_supervisor_cannot_manage_users(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_cashier_cannot_manage_users(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_user_roles(): void
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->admin->isSupervisor());
        $this->assertFalse($this->admin->isCashier());

        $this->assertFalse($this->supervisor->isAdmin());
        $this->assertTrue($this->supervisor->isSupervisor());
        $this->assertFalse($this->supervisor->isCashier());

        $this->assertFalse($this->cashier->isAdmin());
        $this->assertFalse($this->cashier->isSupervisor());
        $this->assertTrue($this->cashier->isCashier());
    }

    public function test_user_email_must_be_unique(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Duplicate Email',
            'email' => $this->cashier->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kasir',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_password_is_hashed(): void
    {
        $userData = [
            'name' => 'Hashed Password',
            'email' => 'hashed@example.com',
            'password' => 'plainpassword',
            'password_confirmation' => 'plainpassword',
            'role' => 'kasir',
            'is_active' => true,
        ];

        $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $user = User::where('email', 'hashed@example.com')->first();

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    public function test_admin_can_deactivate_user(): void
    {
        $user = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_guest_cannot_access_users(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'role' => 'kasir',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
