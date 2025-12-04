<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_forgot_password_page()
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
    }

    /** @test */
    public function user_can_request_password_reset_link()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function user_cannot_request_reset_with_invalid_email()
    {
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function user_can_view_password_reset_form()
    {
        $token = Str::random(64);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => 'test@example.com',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
    }

    /** @test */
    public function user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /** @test */
    public function password_reset_requires_strong_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function admin_can_reset_user_password()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->post(route('users.reset-password', $user->id), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /** @test */
    public function regular_user_cannot_reset_other_user_password()
    {
        $user1 = User::factory()->create(['role' => 0]);
        $user2 = User::factory()->create(['role' => 0]);

        $this->actingAs($user1);

        $response = $this->post(route('users.reset-password', $user2->id), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(403);
    }
}
