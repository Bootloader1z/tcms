<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing encrypted passwords to hashed passwords
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            try {
                // Try to decrypt the password
                $decryptedPassword = Crypt::decryptString($user->password);
                // Hash it properly
                $hashedPassword = Hash::make($decryptedPassword);
                // Update the user
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['password' => $hashedPassword]);
            } catch (\Exception $e) {
                // If decryption fails, the password might already be hashed
                // Log this for manual review
                \Log::warning("Could not migrate password for user {$user->id}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible
        \Log::warning('Password migration rollback is not supported');
    }
};
