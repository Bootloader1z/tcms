<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_login_page()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('login');
    }

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->post(route('login.submit'), [
            'username' => 'testuser',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

    /** @test */
    public function user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->post(route('login.submit'), [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_is_rate_limited_after_too_many_attempts()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('Password123!'),
        ]);

        // Make 6 failed login attempts
        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->post(route('login.submit'), [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Too many login attempts', session('error'));
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /** @test */
    public function user_active_status_is_updated_on_login()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('Password123!'),
            'isactive' => 0,
        ]);

        $this->post(route('login.submit'), [
            'username' => 'testuser',
            'password' => 'Password123!',
        ]);

        $this->assertEquals(1, $user->fresh()->isactive);
    }

    /** @test */
    public function user_active_status_is_updated_on_logout()
    {
        $user = User::factory()->create(['isactive' => 1]);

        $this->actingAs($user);
        $this->get(route('logout'));

        $this->assertEquals(0, $user->fresh()->isactive);
    }
}
