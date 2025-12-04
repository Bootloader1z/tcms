<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user()
    {
        $user = User::factory()->create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_hashes_password_on_creation()
    {
        $user = User::factory()->create([
            'password' => 'Password123!',
        ]);

        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    /** @test */
    public function it_formats_fullname_correctly()
    {
        $user = User::factory()->create([
            'fullname' => 'john doe',
        ]);

        $this->assertEquals('John Doe', $user->fullname);
    }

    /** @test */
    public function it_formats_username_to_lowercase()
    {
        $user = User::factory()->create([
            'username' => 'TestUser',
        ]);

        $this->assertEquals('testuser', $user->username);
    }

    /** @test */
    public function it_can_check_if_user_is_admin()
    {
        $admin = User::factory()->create(['role' => 1]);
        $superAdmin = User::factory()->create(['role' => 9]);
        $user = User::factory()->create(['role' => 0]);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($superAdmin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function it_can_check_if_user_is_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 9]);
        $admin = User::factory()->create(['role' => 1]);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($admin->isSuperAdmin());
    }

    /** @test */
    public function it_can_scope_active_users()
    {
        User::factory()->create(['isactive' => 1]);
        User::factory()->create(['isactive' => 0]);

        $activeUsers = User::active()->get();

        $this->assertCount(1, $activeUsers);
    }
}
