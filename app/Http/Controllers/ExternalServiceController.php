<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExternalService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class ExternalServiceController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $data = ExternalService::where('user_id', '=', auth()->user()->id)->select('id', 'service_name', 'service_price')->where(function ($query) use ($request) {
                if ($request->search != null) {
                    $query->where('service_name', 'like', '%' . $request->search . '%');
                }
            })->orderBy('id', 'DESC')->paginate($request->itemsPerPage ?? 10);
            return $this->sendResponse($data, 'External Service retrieved successfully.');
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
                'service_name' => 'required|max:80',
                'service_price' => 'required|max:80',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['service_name' => $input['service_name'], 'service_price' => $input['service_price'], 'user_id' => auth()->user()->id]);
            ExternalService::create($updateData);
            return $this->sendResponse([], 'External Service created successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function show($id)
    {
        //Using Try & Catch For Error Handling
        try {
            $data = ExternalService::where('id', $id)->select('id', 'service_name', 'service_price')->first();
            return $this->sendResponse($data, 'External Service retrieved successfully.');
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
                'service_name' => 'required|max:80',
                'service_price' => 'required|max:80',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['service_name' => $input['service_name'], 'service_price' => $input['service_price'], 'user_id' => auth()->user()->id]);
            ExternalService::where('id', $id)->update($updateData);
            return $this->sendResponse([], 'External Service updated successfully.');
        } catch (Exception $e) {
            return $e;
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function delete($id)
    {
        //Using Try & Catch For Error Handling
        try {
            DB::table('external_services')->where('id', $id)->delete();
            return $this->sendResponse([], 'External Service deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }
}
