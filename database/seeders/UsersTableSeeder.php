<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing users
        DB::table('users')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed users with proper bcrypt hashing
        $users = [
            [
                'fullname' => 'Super Admin',
                'username' => 'admin',
                'email' => 'administrator@tas.com',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
                'role' => 9, // Super Admin
                'isactive' => 0,
            ],
            [
                'fullname' => 'System Administrator',
                'username' => 'sysadmin',
                'email' => 'sysadmin@tas.com',
                'password' => Hash::make('SysAdmin@123'),
                'email_verified_at' => now(),
                'role' => 1, // Admin
                'isactive' => 0,
            ],
            [
                'fullname' => 'Mark Administrator',
                'username' => 'mark',
                'email' => 'mark@tas.com',
                'password' => Hash::make('Mark@123'),
                'email_verified_at' => now(),
                'role' => 1, // Admin
                'isactive' => 0,
            ],
            [
                'fullname' => 'Regular User',
                'username' => 'user',
                'email' => 'user@tas.com',
                'password' => Hash::make('User@123'),
                'email_verified_at' => now(),
                'role' => 0, // Regular User
                'isactive' => 0,
            ],
            [
                'fullname' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@tas.com',
                'password' => Hash::make('Test@123'),
                'email_verified_at' => now(),
                'role' => 0, // Regular User
                'isactive' => 0,
            ],
        ];

        // Insert each user
        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('');
        $this->command->info('Default Credentials:');
        $this->command->info('Super Admin - Username: admin, Password: Admin@123');
        $this->command->info('Admin - Username: sysadmin, Password: SysAdmin@123');
        $this->command->info('Admin - Username: mark, Password: Mark@123');
        $this->command->info('User - Username: user, Password: User@123');
        $this->command->info('Test User - Username: testuser, Password: Test@123');
        $this->command->info('');
        $this->command->warn('⚠️  IMPORTANT: Change these passwords immediately after first login!');
    }
}
