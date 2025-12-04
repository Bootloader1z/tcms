<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_middleware_allows_admin_users()
    {
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_middleware_allows_super_admin_users()
    {
        $superAdmin = User::factory()->create(['role' => 9]);

        $this->actingAs($superAdmin);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_middleware_blocks_regular_users()
    {
        $user = User::factory()->create(['role' => 0]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_middleware_allows_regular_users()
    {
        $user = User::factory()->create(['role' => 0]);

        $this->actingAs($user);

        // Assuming there's a user-specific route
        $response = $this->get('/user/index');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_middleware_blocks_admin_users()
    {
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin);

        $response = $this->get('/user/index');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_users_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }
}
