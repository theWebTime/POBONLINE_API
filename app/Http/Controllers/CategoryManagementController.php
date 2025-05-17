<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryManagement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class CategoryManagementController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $data = CategoryManagement::where('user_id', '=', auth()->user()->id)->select('id', 'category_role', 'category_price')->where(function ($query) use ($request) {
                if ($request->search != null) {
                    $query->where('category_role', 'like', '%' . $request->search . '%');
                }
            })->orderBy('id', 'DESC')->paginate($request->itemsPerPage ?? 10);
            return $this->sendResponse($data, 'Categories retrieved successfully.');
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
                'category_role' => 'required|max:80',
                'category_price' => 'required|max:80',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['category_role' => $input['category_role'], 'category_price' => $input['category_price'], 'user_id' => auth()->user()->id]);
            CategoryManagement::create($updateData);
            return $this->sendResponse([], 'Category created successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function show($id)
    {
        //Using Try & Catch For Error Handling
        try {
            $data = CategoryManagement::where('id', $id)->select('id', 'category_role', 'category_price')->first();
            return $this->sendResponse($data, 'Category retrieved successfully.');
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
                'category_role' => 'required|max:80',
                'category_price' => 'required|max:80',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['category_role' => $input['category_role'], 'category_price' => $input['category_price'], 'user_id' => auth()->user()->id]);
            CategoryManagement::where('id', $id)->update($updateData);
            return $this->sendResponse([], 'Category updated successfully.');
        } catch (Exception $e) {
            return $e;
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function delete($id)
    {
        //Using Try & Catch For Error Handling
        try {
            DB::table('category_management')->where('id', $id)->delete();
            return $this->sendResponse([], 'Category deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }
}
