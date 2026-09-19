<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin_hrd')->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur ini hanya dapat diakses oleh Administrator.');
        }

        return $next($request);
    }
}
