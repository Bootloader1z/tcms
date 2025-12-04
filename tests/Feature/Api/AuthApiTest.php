<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_via_api()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'username' => 'testuser',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token',
                'token_type',
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function user_can_register_via_api()
    {
        $response = $this->postJson('/api/v1/register', [
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user',
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function authenticated_user_can_logout_via_api()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);
    }

    /** @test */
    public function authenticated_user_can_get_their_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }
}
