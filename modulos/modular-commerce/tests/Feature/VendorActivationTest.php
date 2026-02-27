<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_activate_vendor_mode(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post(route('vendor.activate'), [
            'store_name' => 'Test Store',
            'description' => 'Test store description',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vendor profile activated successfully!',
        ]);

        $this->assertDatabaseHas('vendors', [
            'user_id' => $user->id,
            'store_name' => 'Test Store',
            'status' => 'pending',
        ]);

        $user->refresh();
        $this->assertEquals('vendor', $user->role);
    }

    public function test_user_cannot_activate_vendor_twice(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        Vendor::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('vendor.activate'), [
            'store_name' => 'Another Store',
            'description' => 'Another description',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'User already has a vendor profile.',
        ]);
    }

    public function test_guest_cannot_activate_vendor(): void
    {
        $response = $this->post(route('vendor.activate'), [
            'store_name' => 'Test Store',
            'description' => 'Test description',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_vendor_activation_requires_valid_data(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post(route('vendor.activate'), [
            'store_name' => '', // Invalid: empty
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['store_name']);
    }

    public function test_vendor_activation_creates_unique_slug(): void
    {
        $user1 = User::factory()->create(['role' => 'customer']);
        $user2 = User::factory()->create(['role' => 'customer']);

        // First vendor
        $this->actingAs($user1)->post(route('vendor.activate'), [
            'store_name' => 'Test Store',
            'description' => 'First store',
        ]);

        // Second vendor with same name
        $response = $this->actingAs($user2)->post(route('vendor.activate'), [
            'store_name' => 'Test Store',
            'description' => 'Second store',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('vendors', [
            'user_id' => $user1->id,
            'store_slug' => 'test-store',
        ]);

        $this->assertDatabaseHas('vendors', [
            'user_id' => $user2->id,
            'store_slug' => 'test-store-1',
        ]);
    }

    public function test_user_role_changes_to_vendor_after_activation(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->post(route('vendor.activate'), [
            'store_name' => 'Test Store',
            'description' => 'Test description',
        ]);

        $user->refresh();
        $this->assertEquals('vendor', $user->role);
        $this->assertTrue($user->isVendor());
        $this->assertFalse($user->isCustomer());
    }
}
