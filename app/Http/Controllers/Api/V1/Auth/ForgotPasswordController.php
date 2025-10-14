<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function forgot()
    {
        $credentials = request()->validate(['email' => 'required|email']);

        if (!User::recordExists($credentials['email'], 'email')) throw ValidationException::withMessages(['email' => 'The email is not registered with us.']);

        Password::broker()->sendResetLink($credentials);

        return $this->response(Response::HTTP_OK, __('Reset password link sent on your email'), $data = [], $errors = []);

    }
}
