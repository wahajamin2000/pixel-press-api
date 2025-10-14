<?php

namespace App\Http\Middleware\Custom;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->level, [User::LEVEL_SUPER_ADMIN, User::LEVEL_CUSTOMER])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden. Super Admin & Customer only.'], 403);
            }

            abort(403, 'Forbidden. Super Admin & Customer only.');
        }

        return $next($request);
    }
}
