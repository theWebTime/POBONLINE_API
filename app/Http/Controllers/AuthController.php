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
        if(Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])){ 
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
            $user = User::where('id', $auth->id)->select('id', 'name', 'address', 'phone_number', 'email', 'image', 'instagram_link', 'facebook_link', 'youtube_link', 'website_link', 'studio_name')->first();
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
            $input = $request->all();

            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'address' => 'required|string',
                'instagram_link' => 'nullable|string',
                'facebook_link' => 'nullable|string',
                'youtube_link' => 'nullable|string',
                'website_link' => 'nullable|string',
                'password' => 'nullable|min:6|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|max:20',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:5120',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Get existing user
            // $user = User::findOrFail($id);

            // Prepare update data
            $updateData = [
                'name' => $input['name'],
                'studio_name' => $input['studio_name'],
                'address' => $input['address'],
                'instagram_link' => $input['instagram_link'] ?? '',
                'facebook_link' => $input['facebook_link'] ?? '',
                'youtube_link' => $input['youtube_link'] ?? '',
                'website_link' => $input['website_link'] ?? '',
            ];

            // Only update password if filled
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            // Handle image upload if provided
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path('quotation_hub/images/user'), $filename);
                $updateData['image'] = $filename;
            }

            // Update user
            User::where('id', $user->id)->update($updateData);

            return $this->sendResponse([], 'Profile updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', $e);
        }
    }
}
