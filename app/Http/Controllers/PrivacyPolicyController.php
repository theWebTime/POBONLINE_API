<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class PrivacyPolicyController extends BaseController
{
    public function updateOrCreate(Request $request)
    {
        try {
            $input = $request->all();
            $userId = auth()->user()->id;
            
            PrivacyPolicy::updateOrCreate(
                ['user_id' => $userId],
                ['privacy_policy' => $input['privacy_policy'] ?? '']
            );

            return $this->sendResponse([], 'Privacy Policy Updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong!', $e);
        }
    }

    public function show()
    {
        try {
            $userId = auth()->user()->id;
            $data = PrivacyPolicy::where('user_id', $userId)->select('id', 'privacy_policy')->first();
            if (is_null($data)) {
                return $this->sendError('Privacy Policy not found.');
            }
            return $this->sendResponse($data, 'Privacy Policy retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }
}
