<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request and check if user has permitted role.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Ensure user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Silakan masuk ke portal terlebih dahulu untuk mengakses fitur ini.',
            ]);
        }

        $user = Auth::user();

        // 2. Prevent access if account is locked
        if ($user->isLocked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda saat ini terkunci demi alasan keamanan. Silakan coba lagi nanti atau hubungi Administrator.',
            ]);
        }

        // 3. Verify user role
        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki izin untuk peran '.$user->role.'.',
                ], 403);
            }

            abort(403, 'Akses Ditolak: Peran ['.$user->role.'] tidak memiliki otorisasi untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
