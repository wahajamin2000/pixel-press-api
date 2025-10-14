<?php

namespace App\Http\Controllers\Api\V1\Modules\User;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Auth\ProfileResource;
use App\Http\Resources\Lookups\UserLookupResource;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $users = $this->getAllCustomers($request);

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'users' => UserLookupResource::collection($users)
        ]);
    }

    public function getAllCustomers($request = [])
    {
        $search = $request->search ?? '';

        $query = User::query()->customers();

        if (!empty($search)) {
            $query->where('first_name', 'like', $search . '%')
                ->orWhere('last_name', 'like', $search . '%')
                ->orWhere('email', 'like', $search . '%');
        }

        return $query->get();
    }

    public function show($id)
    {
        $user = User::where('id', $id)->first();

        if( !isset($user) || $user == '' || $user == null){
            return $this->response(Response::HTTP_OK, __('No User Found'), []);
        }

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'user' => new ProfileResource($user),
        ]);
    }

}
