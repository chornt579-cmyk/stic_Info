<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the Login and Registration form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $departments = Department::with('classes')->get();
        return view('auth.login', compact('departments'));
    }

    /**
     * Handle an incoming authentication attempt with rate limiting.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        // Rate Limiting: Max 5 attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            AuditLog::record('auth.login_throttled', null, [
                'email' => $request->input('email'),
                'seconds' => $seconds,
            ]);

            return back()->withErrors([
                'email' => __('messages.throttle_error', ['seconds' => $seconds]),
            ])->onlyInput('email');
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            // Status Verification
            if ($user->status === 'suspended') {
                AuditLog::record('auth.login_blocked_suspended', $user);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => __('messages.suspended_desc'),
                ]);
            }

            if ($user->status === 'rejected') {
                AuditLog::record('auth.login_blocked_rejected', $user);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => __('messages.rejected_desc'),
                ]);
            }

            if ($user->status === 'pending') {
                AuditLog::record('auth.login_pending', $user);
                return redirect()->route('pending.approval');
            }

            AuditLog::record('auth.login_success', $user);
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        AuditLog::record('auth.failed_login', null, [
            'email' => $request->input('email'),
        ]);

        return back()->withErrors([
            'email' => __('messages.auth_failed'),
        ])->onlyInput('email');
    }

    /**
     * Handle account registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->whereNull('deleted_at'), // Respect soft deletes
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'department_id' => ['required', 'exists:departments,id'],
            'class_id' => ['nullable', 'required_if:role,student', 'exists:classes,id'],
        ]);

        $status = ($request->role === 'teacher') ? 'pending' : 'active';
        $approvedAt = ($request->role === 'student') ? now() : null;

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'department_id' => $request->department_id,
            'class_id' => $request->class_id,
        ]);
        $user->role = $request->role;
        $user->status = $status;
        $user->approved_at = $approvedAt;
        $user->save();

        AuditLog::record('auth.registered', $user, [
            'role' => $user->role,
            'status' => $user->status,
        ]);

        Auth::login($user);

        if ($user->status === 'pending') {
            return redirect()->route('pending.approval');
        }

        return redirect()->route('dashboard')->with('success', 'ចុះឈ្មោះបានជោគជ័យ!');
    }

    /**
     * Show pending approval screen for teachers.
     */
    public function pendingApproval()
    {
        $user = Auth::user();
        if ($user && $user->status === 'active') {
            return redirect()->route('dashboard');
        }

        return view('auth.pending');
    }

    /**
     * Handle user logout with audit logging.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::record('auth.logout', Auth::user());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'បានចាកចេញដោយជោគជ័យ!');
    }

    /**
     * Handle password change with session invalidation & token revocation.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->save();

        // Security Protocol: Invalidate other active sessions
        Auth::logoutOtherDevices($request->password);

        // Revoke Sanctum tokens if available
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        AuditLog::record('password.changed', $user);

        return back()->with('success', 'លេខសម្ងាត់ត្រូវបានផ្លាស់ប្ដូរដោយជោគជ័យ! គ្រប់ឧបករណ៍ផ្សេងទៀតត្រូវបានចាកចេញដោយស្វ័យប្រវត្តិ។');
    }

    /**
     * Show Forgot Password form.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle Password Reset link creation.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        AuditLog::record('auth.password_reset_requested', null, [
            'email' => $request->email,
        ]);

        // Provide direct reset route link in session for demonstration/development
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        return back()->with('status', 'តំណភ្ជាប់សម្រាប់កំណត់លេខសម្ងាត់ឡើងវិញត្រូវបានបង្កើត!')
            ->with('dev_reset_url', $resetUrl);
    }

    /**
     * Show Reset Password form.
     */
    public function showResetPassword(Request $request, string $token)
    {
        $email = $request->query('email');
        return view('auth.reset-password', compact('token', 'email'));
    }

    /**
     * Handle Password Reset submission.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'តំណភ្ជាប់កំណត់លេខសម្ងាត់ឡើងវិញមិនត្រឹមត្រូវ ឬផុតកំណត់ឡើយ។']);
        }

        // Token expiry: 60 minutes
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'តំណភ្ជាប់កំណត់លេខសម្ងាត់ឡើងវិញបានផុតកំណត់ហើយ។']);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        // Invalidate token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Log audit event
        AuditLog::record('auth.password_reset_success', $user);

        return redirect()->route('login')->with('success', 'លេខសម្ងាត់ត្រូវបានកំណត់ឡើងវិញដោយជោគជ័យ! សូមចូលប្រើប្រាស់។');
    }
}
