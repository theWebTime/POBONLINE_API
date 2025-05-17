<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookDemo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class BookDemoController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $data = BookDemo::select('id', 'name', 'phone_number', 'demo_status')->orderBy('id', 'DESC')->paginate($request->itemsPerPage ?? 10);
            return $this->sendResponse($data, 'Booked Demo retrieved successfully.');
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
                'name' => 'required|max:80',
                'phone_number' => 'required|max:15',
                'email' => 'required|max:100',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['name' => $input['name'], 'phone_number' => $input['phone_number'], 'email' => $input['email'], 'demo_status' => '0']);
            BookDemo::create($updateData);
            return $this->sendResponse([], 'Demo created successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function show($id)
    {
        //Using Try & Catch For Error Handling
        try {
            $data = BookDemo::where('id', $id)->select('id', 'name', 'phone_number', 'email', 'demo_status')->first();
            return $this->sendResponse($data, 'Booked Demo retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function update(Request $request, $id)
    {
        //Using Try & Catch For Error Handling
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'demo_status' => 'nullable|min:0,1',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['demo_status' => $input['demo_status']]);
            BookDemo::where('id', $id)->update($updateData);
            return $this->sendResponse([], 'Booked Demo updated successfully.');
        } catch (Exception $e) {
            return $e;
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function delete($id)
    {
        //Using Try & Catch For Error Handling
        try {
            DB::table('book_demos')->where('id', $id)->delete();
            return $this->sendResponse([], 'Booked Demo deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }
}
