<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
// use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $data = User::where('role' , 2)->select('id', 'name', 'email', 'status', 'subscription_date', 'subscription_end_date')->where(function ($query) use ($request) {
                if ($request->search != null) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                }
            })->orderBy('id', 'ASC')->paginate($request->itemsPerPage ?? 10);

                    // Format the date fields
            $data->getCollection()->transform(function ($user) {
                $user->subscription_date = $user->subscription_date ? \Carbon\Carbon::parse($user->subscription_date)->format('d-m-Y') : null;
                $user->subscription_end_date = $user->subscription_end_date ? \Carbon\Carbon::parse($user->subscription_end_date)->format('d-m-Y') : null;
                return $user;
            });

            return $this->sendResponse($data, 'User Data retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function store(Request $request)
    {
        //Using Try & Catch For Error Handling
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:5120',
                'name' => 'required|max:100',
                'phone_number' => 'required|max:15',
                'studio_name' => 'required|max:100',
                'address' => 'required|string',
                'email' => 'required|max:100',
                'password' => 'required|min:6|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|max:20',
                'c_password' => 'required|same:password',
                'subscription_date' => 'nullable|max:10',
                'instagram_link' => 'nullable',
                'facebook_link' => 'nullable',
                'youtube_link' => 'nullable',
                'website_link' => 'nullable',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Convert subscription_date and calculate subscription_end_date
            $subscriptionDate = isset($input['subscription_date']) ? \Carbon\Carbon::createFromFormat('Y-m-d', $input['subscription_date']) : '';
            $subscriptionEndDate = $subscriptionDate ? $subscriptionDate->copy()->addYear()->format('Y-m-d') : '';


            $updateData = (['name' => $input['name'], 'phone_number' => $input['phone_number'], 'studio_name' => $input['studio_name'], 'address' => $input['address'], 'email' => $input['email'], 'password' => bcrypt($input['password']), 'subscription_date' => $subscriptionDate ? $subscriptionDate->format('Y-m-d') : '', 'subscription_end_date' => $subscriptionEndDate, 'instagram_link' => $input['instagram_link'] ?? '', 'facebook_link' => $input['facebook_link'] ?? '', 'youtube_link' => $input['youtube_link'] ?? '', 'website_link' => $input['website_link'] ?? '', 'status' => '1']);
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path('images/user'), $filename);
                $updateData['image'] = $filename;
            }
            User::create($updateData);
            return $this->sendResponse([], 'User created successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function registerStore(Request $request)
    {
        //Using Try & Catch For Error Handling
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:5120',
                'name' => 'required|max:100',
                'phone_number' => 'required|max:15',
                'studio_name' => 'required|max:100',
                'address' => 'required|string',
                'email' => 'required|max:100',
                'password' => 'required|min:6|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|max:20',
                'c_password' => 'required|same:password',
                'subscription_date' => 'nullable|max:10',
                'instagram_link' => 'nullable',
                'facebook_link' => 'nullable',
                'youtube_link' => 'nullable',
                'website_link' => 'nullable',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

             // Convert subscription_date and calculate subscription_end_date
            $subscriptionDate = isset($input['subscription_date']) ? \Carbon\Carbon::createFromFormat('Y-m-d', $input['subscription_date']) : '';
            $subscriptionEndDate = $subscriptionDate ? $subscriptionDate->copy()->addYear()->format('Y-m-d') : '';

            $updateData = (['name' => $input['name'], 'phone_number' => $input['phone_number'], 'studio_name' => $input['studio_name'], 'address' => $input['address'], 'email' => $input['email'], 'password' => bcrypt($input['password']), 'subscription_date' => $subscriptionDate ? $subscriptionDate->format('Y-m-d') : '', 'subscription_end_date' => $subscriptionEndDate, 'instagram_link' => $input['instagram_link'] ?? '', 'facebook_link' => $input['facebook_link'] ?? '', 'youtube_link' => $input['youtube_link'] ?? '', 'website_link' => $input['website_link'] ?? '', 'status' =>'0']);
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path('images/user'), $filename);
                $updateData['image'] = $filename;
            }
            User::create($updateData);
            return $this->sendResponse([], 'Registration successfull.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function show($id)
    {
        //Using Try & Catch For Error Handling
        try {
            $data = User::where('id', $id)->select('id', 'name', 'phone_number', 'studio_name', 'image', 'address', 'email',
               'status', 'subscription_date', 'subscription_end_date', 'instagram_link', 'facebook_link' , 'youtube_link', 'website_link')->first();
            return $this->sendResponse($data, 'User Data retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:5120',
                'name' => 'required|max:100',
                'phone_number' => 'required|max:15',
                'studio_name' => 'required|max:100',
                'address' => 'required|string',
                'email' => 'required|max:100',
                'password' => 'nullable|min:6|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|max:20',
                'status' => 'required|in:0,1',
                'subscription_date' => 'required|max:10',
                'subscription_end_date' => 'required|max:10',
                'instagram_link' => 'nullable|string',
                'facebook_link' => 'nullable|string',
                'youtube_link' => 'nullable|string',
                'website_link' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Get existing user
            $user = User::findOrFail($id);

            // Prepare update data
            $updateData = [
                'name' => $input['name'],
                'phone_number' => $input['phone_number'],
                'studio_name' => $input['studio_name'],
                'address' => $input['address'],
                'email' => $input['email'],
                'status' => $input['status'],
                'subscription_date' => $input['subscription_date'],
                'subscription_end_date' => $input['subscription_end_date'],
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
                $file->move(public_path('images/user'), $filename);
                $updateData['image'] = $filename;
            }

            // Update user
            $user->update($updateData);

            return $this->sendResponse([], 'User updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', $e->getMessage());
        }
    }


    public function delete($id)
    {
        //Using Try & Catch For Error Handling
        try {
            $data = DB::table('users')->where('id', $id)->first();
            $path = public_path() . "/images/user/" . $data->image;
            unlink($path);
            DB::table('users')->where('id', $id)->delete();
            return $this->sendResponse([], 'User deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }
}
