<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $departments = Department::with('classes')->get();
        return view('auth.login', compact('departments'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->status === 'pending') {
                return redirect()->route('pending.approval');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'អ៊ីមែល ឬលេខសម្ងាត់មិនត្រឹមត្រូវឡើយ។',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'department_id' => ['required', 'exists:departments,id'],
            'class_id' => ['nullable', 'required_if:role,student', 'exists:classes,id'],
        ]);

        // Students active immediately, Teachers pending approval
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

        Auth::login($user);

        if ($user->status === 'pending') {
            return redirect()->route('pending.approval');
        }

        return redirect()->route('dashboard')->with('success', 'ចុះឈ្មោះបានជោគជ័យ!');
    }

    public function pendingApproval()
    {
        $user = Auth::user();
        if ($user && $user->status === 'active') {
            return redirect()->route('dashboard');
        }

        return view('auth.pending');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
