<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParticularFunction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class ParticularFunctionController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $data = ParticularFunction::where('user_id', '=', auth()->user()->id)->select('id', 'name', 'is_multiple_days')->where(function ($query) use ($request) {
                if ($request->search != null) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                }
            })->orderBy('id', 'DESC')->paginate($request->itemsPerPage ?? 10);
            return $this->sendResponse($data, 'Particular Functions retrieved successfully.');
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
                'name' => 'required|max:70',
                'is_multiple_days' => 'required|in:0,1',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['name' => $input['name'], 'is_multiple_days' => $input['is_multiple_days'], 'user_id' => auth()->user()->id]);
            ParticularFunction::create($updateData);
            return $this->sendResponse([], 'Particular Function created successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function show($id)
    {
        //Using Try & Catch For Error Handling
        try {
            $data = ParticularFunction::where('id', $id)->select('id', 'name', 'is_multiple_days')->first();
            return $this->sendResponse($data, 'Particular Function successfully.');
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
                'name' => 'required|max:70',
                'is_multiple_days' => 'required|in:0,1',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['name' => $input['name'], 'is_multiple_days' => $input['is_multiple_days'], 'user_id' => auth()->user()->id]);
            ParticularFunction::where('id', $id)->update($updateData);
            return $this->sendResponse([], 'Particular Function updated successfully.');
        } catch (Exception $e) {
            return $e;
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function delete($id)
    {
        //Using Try & Catch For Error Handling
        try {
            DB::table('particular_functions')->where('id', $id)->delete();
            return $this->sendResponse([], 'Particular Function deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }
}
