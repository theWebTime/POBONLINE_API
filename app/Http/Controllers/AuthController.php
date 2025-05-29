<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;
        return $this->sendResponse($success, 'User register successfully.');
    }

    public function login(Request $request): JsonResponse
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
             if ($user->status == 0) {
                return $this->sendError('Unauthorized.');
            }
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $success['name'] =  $user->name;
            $success['role'] =  $user->role;
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    public function logout()
    {
        try {
            if (Auth::user()) {
                $user = Auth::user()->token();
                $user->revoke();
                return $this->sendResponse([], 'User logout successfully.');
            } else {
                return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
            }
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function profile()
    {
        try {
            $auth = auth()->user();
            if (is_null($auth)) {
                return $this->sendError('Profile not found.');
            }
            $user = User::where('id', $auth->id)->select('id', 'name', 'email', 'image', 'instagram_link', 'facebook_link', 'youtube_link', 'website_link', 'studio_name')->first();
            return $this->sendResponse($user, 'Profile retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    /* public function profile_update(Request $request)
    {
        try {
            $user = auth()->user();

            $input = $request->except(['password']);
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'instagram_link' => 'nullable',
                'facebook_link' => 'nullable',
                'youtube_link' => 'nullable',
                'website_link' => 'nullable',
                // 'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|min:6|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|max:20',
            ]);
            if ($request->password) {
                $input['password'] = bcrypt($request->password);
            }
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            User::where('id', $user->id)->update($input);
            return $this->sendResponse([], 'Profile updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    } */

    public function profile_update(Request $request)
    {
        try {
            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'instagram_link' => 'nullable|string',
                'facebook_link' => 'nullable|string',
                'youtube_link' => 'nullable|string',
                'website_link' => 'nullable|string',
                'password' => 'nullable|min:6|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|max:20',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $input = $request->except(['password']);

            // Convert empty strings to null for optional social links
            $nullableFields = ['instagram_link', 'facebook_link', 'youtube_link', 'website_link'];
            foreach ($nullableFields as $field) {
                if (isset($input[$field]) && $input[$field] === '') {
                    $input[$field] = '';
                }
            }

            if ($request->filled('password')) {
                $input['password'] = bcrypt($request->password);
            }

            User::where('id', $user->id)->update($input);

            return $this->sendResponse([], 'Profile updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', $e);
        }
    }
}
