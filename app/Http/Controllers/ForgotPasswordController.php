<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
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
            'email' => 'required|email',
        ], [
            'email.required' => 'Mangyaring ilagay ang iyong email address.',
            'email.email' => 'Mangyaring maglagay ng wastong email format.',
        ]);

        $email = trim($request->input('email'));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withInput()->withErrors([
                'email' => 'Walang account na natagpuan gamit ang email address na ito.',
            ]);
        }

        try {
            // Generate a secure random token
            $rawToken = Str::random(64);

            // Remove existing reset records for this email
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            // Insert new token record (storing hashed token for security)
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => Hash::make($rawToken),
                'created_at' => Carbon::now(),
            ]);

            $resetUrl = route('password.reset', [
                'token' => $rawToken,
                'email' => $email,
            ]);

            // Send Email
            Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));

            return back()->with('success', 'Naipadala na ang password reset link sa iyong email! Pakitingnan ang iyong inbox o spam folder.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors([
                'email' => 'Hindi maipadala ang email sa ngayon: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the password reset view for the given token.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', old('email')),
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'email.required' => 'Mangyaring ilagay ang iyong email address.',
            'password.required' => 'Mangyaring ilagay ang iyong bagong password.',
            'password.min' => 'Ang password ay dapat hindi bababa sa 6 na characters.',
            'password.confirmed' => 'Hindi magkatugma ang password confirmation.',
        ]);

        $email = trim($request->input('email'));
        $token = $request->input('token');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return back()->withInput()->withErrors([
                'email' => 'Invalid o expired na ang reset token. Mangyaring humiling muli ng bagong link.',
            ]);
        }

        // Check if token has expired (60 minutes expiry)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withInput()->withErrors([
                'email' => 'Expired na ang reset link na ito (valid lamang ng 60 minuto). Mangyaring humiling muli ng bago.',
            ]);
        }

        // Verify token hash
        if (!Hash::check($token, $record->token)) {
            return back()->withInput()->withErrors([
                'email' => 'Maling reset token para sa account na ito.',
            ]);
        }

        // Find user and update password
        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withInput()->withErrors([
                'email' => 'Walang account na natagpuan para sa email na ito.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'remember_token' => Str::random(60),
        ])->save();

        // Delete token record after successful reset
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name ?? $user->email,
            'action' => 'PASSWORD_RESET',
            'description' => "User {$user->name} reset their password via email link.",
        ]);

        return redirect()->route('login')->with('success', 'Matagumpay na napalitan ang iyong password! Maaari ka nang mag-login.');
    }
}
