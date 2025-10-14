<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\StatusEnum;
use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Resources\Auth\UserResource;
use Bitrix\Main\Mail\EventMessageThemeCompiler;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthApiController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $this->validateCredentials();
        if (!$this->attemptLogin($credentials)) {
            return $this->response(Response::HTTP_BAD_REQUEST, __('These credentials do not match our records.'), []);
        }

        $user = auth_user('sanctum');

//        if ($user->status != User::KEY_STATUS_ACTIVE) {
//            return $this->response(Response::HTTP_BAD_REQUEST, __('You are not allowed to login'), []);
//        }

        if ($request->fcm_token != null && $request->fcm_token != '') {
            $user->fcm_token = $request->fcm_token ?? null;
            $user->save();
        }

        $user->last_login = now();
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken('access-token')->plainTextToken;

        return $this->response(Response::HTTP_OK, __('Logged In Successfully'), [
            'user' => new UserResource(
                $user,
                ['jwt_token' => $this->tokenInfo($token)]
            ),
        ]);
    }

    private function validateCredentials()
    {
        return request()->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);
    }

    private function attemptLogin($credentials)
    {
        $credentials = [
            'password' => $credentials['password'],
            'email' => $credentials['email']
        ];

        return auth()->attempt($credentials);
    }

    public function register(Request $request)
    {
        $data = $this->userValidation($request, new User());
        $user = $this->storeUser($request, $data);

        $credentials = $this->validateCredentials();
        if (!$this->attemptLogin($credentials)) {
            return $this->response(Response::HTTP_BAD_REQUEST, __('These credentials do not match our records.'), []);
        }

        $user = auth_user('sanctum');

        if ($request->fcm_token != null && $request->fcm_token != '') {
            $user->fcm_token = $request->fcm_token ?? null;
            $user->save();
        }

        $user->last_login = now();
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken('access-token')->plainTextToken;

        return $this->response(Response::HTTP_OK, __('Registered Successfully'), [
            'user' => new UserResource(
                $user,
                ['jwt_token' => $this->tokenInfo($token)]
            ),
        ]);
    }

    private function userValidation(Request $request, User $user)
    {
        $userValidation                     = [];
        $userValidation['first_name']       = ['required'];
        $userValidation['last_name']        = ['nullable'];
        $userValidation['email']            = ['required', 'string', 'email', 'max:255', 'unique:users,email'];
        $userValidation['phone']            = ['nullable'];
        $userValidation['gender']           = ['nullable','integer', Rule::in([User::KEY_GENDER_MALE, User::KEY_GENDER_FEMALE])];
        $userValidation['address_line_one'] = ['required'];
        $userValidation['address_line_two'] = ['nullable'];
        $userValidation['city']             = ['nullable'];
        $userValidation['state']            = ['nullable'];
        $userValidation['post_code']        = ['nullable'];
        $userValidation['password']         = ['required', 'min:13', 'max:30'];

        return $request->validate($userValidation);
    }

    public function storeUser(Request $request, array $data)
    {
        $name = $data['first_name'] .' '. $data['last_name'];

        return User::create([
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'] ?? null,
            'slug'             => generate_slug($name,32,'user-'),
            'email'            => $data['email'],
            'phone'            => $data['phone'] ?? null,
            'gender'           => $data['gender'] ?? null,
            'address_line_one' => $data['address_line_one'] ?? null,
            'address_line_two' => $data['address_line_two'] ?? null,
            'city'             => $data['city'] ?? null,
            'state'            => $data['state'] ?? null,
            'post_code'        => $data['post_code'] ?? null,
            'password'         => Hash::make($data['password']),
            'role'             => User::ROLES[User::LEVEL_CUSTOMER],
            'level'            => User::LEVEL_CUSTOMER,
            'status'           => StatusEnum::Active->value,
        ]);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = auth_user('sanctum');
        $user->tokens()->delete();
        return $this->response(Response::HTTP_OK, __('Logged out Successfully'), []);

//        if (isset($auth_user)) {
//            $fcmToken = User::where('id', '=', $auth_user->id)->first();
//            $fcmToken->fcm_token = '';
//            $fcmToken->save();
//
//            $accessToken = $request->bearerToken();
//            $token = PersonalAccessToken::findToken($accessToken);
//            $token->delete();
//
//            return $this->response(Response::HTTP_OK, __('Logged out Successfully'), []);
//        } else {
//            return $this->response(Response::HTTP_BAD_REQUEST, __('Something Went Wrong'), []);
//        }


    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = $this->guard()->refresh();

        if (isset($token)) {

            return $this->response(Response::HTTP_OK, __('Refreshed Successfully'), [
                'user' => new UserResource($this->guard()->user(), ['jwt_token' => $this->tokenInfo($token)]),
            ]);
        }

        return $this->response(Response::HTTP_BAD_REQUEST, __('Your Current Password in not correct. Please try again'), []);
    }


    protected function tokenInfo($token)
    {
        return [
            'access_token' => $token,
//            'access_token' => auth()->user()->createToken('auth-token')->plainTextToken,
            'token_type' => 'bearer',
//            'expires_in_sec' => Carbon::now()->addYears(2)->timestamp
        ];
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function RespondWithToken($success, $response_code, $message, $data, $token)
    {
        return response()->json([
            'success' => $success,
            'response_code' => $response_code,
            'message' => $message,
            'data' => ['user' => $data],
            'jwt_token' => $token,
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    public function sendNotification(Request $request)
    {
        $deviceToken = $request->fcm_token;
        $notificationTitle = "New Test Notification";
        $notificationDescription = "Testing notification send form local machine";
        (new NotificationController())->sendFCMNotification($deviceToken, null, $notificationTitle, $notificationDescription, null);

        return $this->response(Response::HTTP_OK, __('Notification send successfully!'), []);
    }
}
