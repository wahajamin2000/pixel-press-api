<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Auth\ProfileResource;
use App\Http\Resources\Lookups\UserLookupResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Response;

class ProfileApiController extends Controller
{
    public function show()
    {
        return $this->response(Response::HTTP_OK, __('Fetched Successfully!'), [
           'user' => new ProfileResource(auth()->user()),
        ]);
    }

    public function update(Request $request)
    {
        $user = $this->validateAndUpdate($request);

        return $this->response(Response::HTTP_OK, __('Profile Updated Successfully'), [
            'user' => new ProfileResource($user),
        ]);
    }

    public function validateAndUpdate(Request $request): User
    {
        $user = auth()->user();
        $data = $this->userProfileValidation($request, $user);

        return $this->updateUser($data, $user);
    }

    private function userProfileValidation(Request $request, User $user): array
    {
        $rules = [];
        $rules['first_name']       = ['nullable'];
        $rules['last_name']        = ['nullable'];
        $rules['gender']           = ['nullable','integer', Rule::in([User::KEY_GENDER_MALE, User::KEY_GENDER_FEMALE])];
        $rules['phone']            = ['nullable'];
        $rules['gender']           = ['nullable'];
        $rules['address_line_one'] = ['nullable'];
        $rules['address_line_two'] = ['nullable'];
        $rules['city']             = ['nullable'];
        $rules['state']            = ['nullable'];
        $rules['post_code']        = ['nullable'];

        return $request->validate($rules);
    }

    public function updateUser(array $data, User $user): User
    {
        $user->first_name = $data['first_name'] ?? $user->first_name;
        $user->last_name = $data['last_name'] ?? $user->last_name;
        $user->gender = $data['gender'] ?? $user->gender;
        $user->address_line_one = $data['address_line_one'] ?? $user->address_line_one;
        $user->address_line_two = $data['address_line_two'] ?? $user->address_line_two;
        $user->city = $data['city'] ?? $user->city;
        $user->state = $data['state'] ?? $user->state;
        $user->post_code = $data['post_code'] ?? $user->post_code;
        $user->updated_by = Auth::id();

        $user->save();

        return $user;
    }

    public function delete()
    {
        $user = auth()->user();

        if ($user->level == User::LEVEL_SUPER_ADMIN) return $this->response(Response::HTTP_BAD_REQUEST, __('Cannot delete account'), []);

        $user->forceDelete();

        return $this->response(Response::HTTP_OK, __('Account Deleted Successfully'), []);
    }

    public function changeProfilePicture(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
        ]);

        $user = auth()->user();

        $newPic = $this->storeProfilePicture($request);

        if ($user->pic && $this->isStoredProfilePicture($user->pic)) {
            $this->deleteProfilePicture($user->pic);
        }

        $user->pic = $newPic;
        $user->save();

        return $this->response(Response::HTTP_OK, __('Profile Picture Updated Successfully'), [
            'user' => new UserLookupResource($user),
        ]);
    }

    protected function storeProfilePicture(Request $request): ?string
    {
        $file = $request->file('file');

        if (!$file) {
            return null;
        }

        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        $storedPath = $file->storeAs(config('project.storage.store.images.users'), $filename);

        return asset(config('project.storage.retrieve.images.users') . $filename);
    }

    protected function deleteProfilePicture(string $fullPath): void
    {
        $filename = basename($fullPath); // gets the last part of the path
        $storagePath = config('project.storage.store.images.users') . $filename;

        if (Storage::exists($storagePath)) {
            Storage::delete($storagePath);
        }
    }

    protected function isStoredProfilePicture(string $picUrl): bool
    {
        return str_contains($picUrl, config('project.storage.retrieve.images.users'));
    }

}
