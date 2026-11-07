<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->status === 'pending') {
            return redirect()->route('pending.approval');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'អ្នកមិនមានសិទ្ធិចូលមើលទំព័រនេះឡើយ (Unauthorized Access)។');
        }

        return $next($request);
    }
}
