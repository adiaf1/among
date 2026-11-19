<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessModule($permissions)) {
            abort(403, 'Anda tidak memiliki hak akses (permission) untuk membuka modul ini.');
        }

        return $next($request);
    }
}
