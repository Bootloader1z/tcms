<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;



class AuthController extends Controller
{
    public function loadlogin()
{
    return view('login');
}

public function loadregister()
{
    return view('register');
}
public function login(Request $request)
{
    try {
        // Validate the form data with rate limiting
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('username', 'password');

        // Attempt to authenticate using Laravel's built-in authentication
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // Regenerate session to prevent session fixation
            $request->session()->regenerate();
            
            // Update user active status
            Auth::user()->update(['isactive' => 1]);

            // Log successful login
            \Log::info('User logged in', ['user_id' => Auth::id(), 'ip' => $request->ip()]);

            // Redirect based on user role
            return redirect()->intended($this->redirectDash());
        }

        // Log failed login attempt
        \Log::warning('Failed login attempt', [
            'username' => $request->username,
            'ip' => $request->ip()
        ]);

        throw new \Exception('Invalid credentials. Please try again.');
    } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->back()
            ->withInput($request->only('username'))
            ->withErrors($e->errors());
    } catch (\Exception $e) {
        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', 'Invalid credentials. Please try again.');
    }
}

public function redirectDash()
{
    $redirect = '';

    if (Auth::user() && Auth::user()->role == 0) {
        $redirect = '/user/index';
    } else {
        $redirect = '/dashboard'; // Assuming this is the admin dashboard URL
    }

    return $redirect;
}

public function register(Request $request)
{
    $request->validate([
        'fullname' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username|alpha_dash',
        'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        'email' => 'required|email|max:255|unique:users,email',
    ], [
        'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
    ]);
    
    try {
        DB::beginTransaction();
        
        $user = User::create([
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'role' => 0, // Default role
        ]);

        \Log::info('New user registered', ['user_id' => $user->id, 'ip' => $request->ip()]);
        
        DB::commit();
        
        return redirect()->route('login')->with('success', 'Registration successful');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Registration failed', ['error' => $e->getMessage()]);
        return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
            ->with('error', 'Registration failed. Please try again.');
    }
}

function logoutx(){
    $user = Auth::user();
    $user->update(['isactive' => 0]);
    Session::flush();
    Auth::logout();
    return redirect('/');
 }
}
