<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('user_management', ['users' => $users]);
    }

    public function create()
    {
        return view('add-user');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'fullname' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users|alpha_dash',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                ],
                'role' => 'required|integer|in:0,1,2,9',
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'fullname' => $request->input('fullname'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'role' => $request->input('role'),
                'email_verified_at' => now(),
                'password' => Hash::make($request->input('password')),
            ]);

            Log::info('New user created', [
                'admin_id' => auth()->id(),
                'new_user_id' => $user->id,
                'username' => $user->username,
            ]);

            DB::commit();

            return redirect()->route('user_management')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create user');
        }
    }

    public function destroy(User $user)
    {
        try {
            Log::warning('User deleted', [
                'admin_id' => auth()->id(),
                'deleted_user_id' => $user->id,
                'username' => $user->username,
            ]);

            $user->delete();

            return redirect()->route('user_management')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            Log::error('User deletion failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to delete user');
        }
    }

    public function profile($id)
    {
        $user = User::findOrFail($id);
        return view('profile', ['user' => $user]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('edit_profile', compact('user'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'fullname' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $id,
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            ]);

            $user = User::findOrFail($id);
            $user->update($request->only(['fullname', 'username', 'email']));

            Log::info('User updated', ['user_id' => $id, 'updated_by' => auth()->id()]);

            return redirect()->back()->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            Log::error('User update failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update profile');
        }
    }
}
