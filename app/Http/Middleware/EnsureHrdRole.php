<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureHrdRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin_hrd')->user();

        if (! $user || ! $user->isHrd()) {
            abort(403, 'Akses ditolak. Fitur ini hanya dapat diakses oleh Tim HRD.');
        }

        return $next($request);
    }
}
