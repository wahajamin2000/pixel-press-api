<?php

namespace App\Http\Middleware\Custom;

use App\Enums\StatusEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->trashed() || auth()->check() && auth()->user()->status->value === StatusEnum::Blocked->value) {
            auth()->logout();

            return redirect()->route('login')->with('danger', __('Your account has been deactivated for some reason. please contact support to activate your account'))
                ->withErrors([
                'email' => 'Your account has been deactivated for some reason. please contact support to activate your account',
            ]);
        }

        return $next($request);
    }
}
