<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\YourStory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class YourStoryController extends BaseController
{
    public function updateOrCreate(Request $request)
    {
        try {
            $input = $request->all();
            $updateData = ['user_id' => auth()->user()->id];

            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path('quotation_hub/images/yourStory'), $filename);
                $updateData['image'] = $filename;
            }

            if ($request->file('image2')) {
                $file = $request->file('image2');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path('quotation_hub/images/yourStory'), $filename);
                $updateData['image2'] = $filename;
            }

            YourStory::updateOrInsert(['user_id' => auth()->user()->id], $updateData);

            return $this->sendResponse([], 'Your Story Updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong!', $e);
        }
    }

    public function show()
    {
        try {
            $userId = auth()->user()->id;
            $data = YourStory::where('user_id', $userId)->select('id', 'image', 'image2')->first();
            if (is_null($data)) {
                return $this->sendError('Your Story not found.');
            }
            return $this->sendResponse($data, 'Your Story retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function deleteImage(Request $request)
    {
        try {
            $userId = auth()->id();
            $column = $request->column;

            if (!in_array($column, ['image', 'image2'])) {
                return $this->sendError('Invalid image column.');
            }

            $story = YourStory::where('user_id', $userId)->first();

            if (!$story || !$story->$column) {
                return $this->sendError('Image not found.');
            }

            $filePath = public_path('quotation_hub/images/yourStory/' . $story->$column);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $story->$column = null;
            $story->save();

            return $this->sendResponse([], 'Image deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong!', $e);
        }
    }
}
