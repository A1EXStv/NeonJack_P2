<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
 

class ProfileController extends Controller
{
    /** */
    public function update(UpdateProfileRequest $request)
    {
        $profile = Auth::user();
        $profile->name = $request->name;
        $profile->save();

        return new UserResource($profile);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('roles');
        return new UserResource($user);
    }
}
