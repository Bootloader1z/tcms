<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Display the password reset request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
        ]);

        try {
            // Generate token
            $token = Str::random(64);

            // Delete old tokens for this email
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Create new token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);

            // Get user
            $user = User::where('email', $request->email)->first();

            // Log the reset request
            \Log::info('Password reset requested', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            // In production, send email here
            // Mail::to($user->email)->send(new PasswordResetMail($token, $user));

            // For development, show the reset link
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

            if (config('app.env') === 'local') {
                return redirect()->back()->with('status', 
                    'Password reset link: ' . $resetUrl . ' (This is shown only in development mode)'
                );
            }

            return redirect()->back()->with('status', 
                'We have emailed your password reset link! Please check your email.'
            );

        } catch (\Exception $e) {
            \Log::error('Password reset link generation failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send reset link. Please try again.');
        }
    }

    /**
     * Display the password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        try {
            // Find the token
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                throw ValidationException::withMessages([
                    'email' => ['This password reset token is invalid.'],
                ]);
            }

            // Check if token is expired (60 minutes)
            $createdAt = Carbon::parse($resetRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->delete();

                throw ValidationException::withMessages([
                    'email' => ['This password reset token has expired.'],
                ]);
            }

            // Verify token
            if (!Hash::check($request->token, $resetRecord->token)) {
                throw ValidationException::withMessages([
                    'email' => ['This password reset token is invalid.'],
                ]);
            }

            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Log the password reset
            \Log::info('Password reset successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->with('success', 'Your password has been reset successfully! Please login with your new password.');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Password reset failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput($request->only('email'))
                ->with('error', 'Failed to reset password. Please try again.');
        }
    }

    /**
     * Admin force password reset for a user.
     */
    public function adminResetPassword(Request $request, $userId)
    {
        // Check if user is admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        try {
            $user = User::findOrFail($userId);

            // Prevent resetting super admin password unless you are super admin
            if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
                abort(403, 'You cannot reset a super admin password.');
            }

            $user->password = Hash::make($request->password);
            $user->save();

            // Log the admin password reset
            \Log::warning('Admin password reset', [
                'admin_id' => auth()->id(),
                'admin_username' => auth()->user()->username,
                'target_user_id' => $user->id,
                'target_username' => $user->username,
                'ip' => $request->ip(),
            ]);

            return redirect()->back()
                ->with('success', "Password for {$user->fullname} has been reset successfully.");

        } catch (\Exception $e) {
            \Log::error('Admin password reset failed', [
                'admin_id' => auth()->id(),
                'target_user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reset password. Please try again.');
        }
    }
}
