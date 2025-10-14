<?php

namespace App\Http\Middleware\Custom;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->level !== User::LEVEL_SUPER_ADMIN) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden. Super Admin only.'], 403);
            }

            abort(403, 'Forbidden. Super Admin only.');
        }

        return $next($request);
    }
}
