<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ChangePasswordController extends Controller
{
    public function showChangePassword()
    {
        return null;
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $this->validatePassword($request, $user);

        if ($this->updatePassword($request, $user)) {

            $user->tokens()->delete();
            $token = $user->createToken('access-token')->plainTextToken;

            return $this->response(Response::HTTP_OK, __('Password changed successfully'), [
                'user' => new UserResource(auth()->user(), ['jwt_token' => $this->tokenInfo($token)]),
            ]);
        }

        return $this->response(Response::HTTP_BAD_REQUEST, __('Your Current Password in not correct. Please try again'), []);
    }


    private function authenticateUser(User $user)
    {
        $this->authorize('update', $user);
    }

    protected function validatePassword(Request $request, User $user)
    {
        $this->validate($request, [
            'currentPassword' => 'required',
            'password' => 'required|string|min:6', // |confirmed
        ]);
    }

    private function updatePassword(Request $request, User $user)
    {
        $currentPassword = $user->password;
        if (Hash::check($request->input('currentPassword'), $currentPassword)) {

            if (Hash::check($request->input('password'), $currentPassword)) {
                return $this->response(Response::HTTP_BAD_REQUEST, __('New password cannot be same as current password'), []);
            }

            $user->password = Hash::make($request->input('password'));
            $user->save();

            return true;
        }

        return false;
    }

    protected function tokenInfo($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
//            'expires_in_sec' => Carbon::now()->addweek()->timestamp
//            'expires_in_sec' => auth()->factory()->getTTL() * 60 * 60 * 7
        ];
    }

    public function guard()
    {
        return Auth::guard();
    }
}
