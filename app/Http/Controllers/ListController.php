<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CategoryManagement;
use App\Models\StaffManagement;
use App\Models\ParticularFunction;
use App\Models\ComplimentService;
use App\Models\ClientFunction;
use App\Models\OrganizeDepartment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController as BaseController;

class ListController extends BaseController
{
    public function categoryList()
    {
        try{
            $data = CategoryManagement::where('user_id', auth()->user()->id)->select('id', 'category_role')->get();
            return $this->sendResponse($data, 'Category Names retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    /* public function getAvailableStaff($categoryId, $clientFunctionId)
    {
        try {
            // Get the date of the current function
            $function = ClientFunction::findOrFail($clientFunctionId);
            $date = $function->date;

            // Get staff assigned to other functions with the same date and same category
            $assignedStaffIds = OrganizeDepartment::whereHas('clientFunction', function ($q) use ($date, $clientFunctionId) {
                    $q->where('date', $date)
                    ->where('id', '!=', $clientFunctionId); // Exclude current function
                })
                ->where('category_management_id', $categoryId)
                ->pluck('staff_management_id')
                ->toArray();

            // Get staff of the given category
            $staff = StaffManagement::where('category_role_id', $categoryId)
                ->whereNotIn('id', $assignedStaffIds)
                ->select('id', 'name')
                ->get();

            return response()->json(['success' => true, 'data' => $staff], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    } */

    public function getAvailableStaff($categoryId, $clientFunctionId)
    {
        try {
            if (!$clientFunctionId) {
                // For new unsaved functions, just return all available staff of the given category
                $staff = StaffManagement::where('category_role_id', $categoryId)
                    ->select('id', 'name')
                    ->get();

                return response()->json(['success' => true, 'data' => $staff]);
            }

            // Get the date of the current function
            $function = ClientFunction::findOrFail($clientFunctionId);
            $date = $function->date;

            // Get staff assigned to other functions with the same date and same category
            $assignedStaffIds = OrganizeDepartment::whereHas('clientFunction', function ($q) use ($date, $clientFunctionId) {
                    $q->where('date', $date)
                    ->where('id', '!=', $clientFunctionId); // Exclude current function
                })
                ->where('category_management_id', $categoryId)
                ->pluck('staff_management_id')
                ->toArray();

            // Get available staff of the given category
            $staff = StaffManagement::where('category_role_id', $categoryId)
                ->whereNotIn('id', $assignedStaffIds)
                ->select('id', 'name')
                ->get();

            return response()->json(['success' => true, 'data' => $staff], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /* public function getStaffByCategory(Request $request, $categoryId)
    {
        try{
            $data = StaffManagement::where('category_role_id', $categoryId)->select('id', 'name')->get();
            return $this->sendResponse($data, 'Staff Names retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    } */


   /*  public function getStaffByCategory(Request $request, $categoryId)
    {
        // $date = $request->input('date');
        $clientFunctionId = $request->query('client_function_id'); // ID of the function being edited

        if (!$date) {
            return response()->json(['success' => false, 'message' => 'Date is required.']);
        }

        // Get staff already assigned to other functions on the same date
        $assignedStaffIds = OrganizeDepartment::whereHas('clientFunction', function ($q) use ($date) {
                $q->where('date', $date);
            })
            ->when($clientFunctionId, function ($q) use ($clientFunctionId) {
                // Exclude current function's staff
                $q->where('client_function_id', '!=', $clientFunctionId);
            })
            ->pluck('staff_management_id')
            ->toArray();

        // Get available staff for the category who are NOT assigned on that date (except current function)
        $availableStaff = StaffManagement::where('category_role_id', $categoryId)
            ->whereNotIn('id', $assignedStaffIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $availableStaff
        ]);
    } */

    public function particularFunctionList()
    {
        try{
            $data = ParticularFunction::where('user_id', auth()->user()->id)->select('id', 'name', 'is_multiple_days')->get();
            return $this->sendResponse($data, 'Particular Functions Names retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function complimentServiceList()
    {
        try{
            $data = ComplimentService::where('user_id', auth()->user()->id)->select('id', 'name')->get();
            return $this->sendResponse($data, 'Compliment Services retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function functionWiseOrderCount()
    {
        try {
        $data = DB::table('clients')
            ->join('particular_functions', 'clients.particular_function_id', '=', 'particular_functions.id')
            ->select(
                'particular_functions.name',
                DB::raw('count(clients.id) as total'),
                DB::raw('sum(case when clients.is_booked = 1 then 1 else 0 end) as booked')
            )
            ->where('clients.user_id', auth()->user()->id)
            ->groupBy('particular_functions.name')
            ->get();

        return $this->sendResponse($data, 'Function-wise order count retrieved.');
    } catch (\Exception $e) {
        return $this->sendError('Something went wrong', $e->getMessage());
    }
        /* try {
            $data = DB::table('clients')
                ->join('particular_functions', 'clients.particular_function_id', '=', 'particular_functions.id')
                ->select('particular_functions.name', DB::raw('count(clients.id) as total'))
                ->where('clients.user_id', auth()->user()->id)
                ->groupBy('particular_functions.name')
                ->get();

            return $this->sendResponse($data, 'Function wise order count retrieved.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e);
        } */
    }

    public function totalRegisteredAdmins()
    {
        try {
            $count = DB::table('users')
                ->where('role', 2)
                ->count();

            return response()->json([
                'success' => true,
                'data' => $count,
                'message' => 'Total registered admins fetched successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dashboardData()
    {
        $user = auth()->user();

        if ($user->role == 1) {
            // Super Admin: Show total registered users (role = 2)
            $totalAdmins = User::where('role', 2)->count();
            $activateAdmins = User::where('status', 1)->count();
            $inActivateAdmins = User::where('status', 0)->count();

            return response()->json([
                'success' => true,
                'role' => 1,
                'data' => [
                    'total_admins' => $totalAdmins,
                    'activate_admins' => $activateAdmins,
                    'in_activate_admins' => $inActivateAdmins,
                ]
            ]);
        } elseif ($user->role == 2) {
            // Admin: Show function-wise order count
            $orderStats = DB::table('clients')
                ->join('particular_functions', 'clients.particular_function_id', '=', 'particular_functions.id')
                ->select(
                    'particular_functions.name',
                    DB::raw('COUNT(clients.id) as total'),
                    DB::raw('SUM(CASE WHEN clients.is_booked = 1 THEN 1 ELSE 0 END) as booked')
                )
                ->where('clients.user_id', $user->id)
                ->groupBy('particular_functions.name')
                ->get();

            return response()->json([
                'success' => true,
                'role' => 2,
                'data' => $orderStats
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized role'
        ], 403);
    }
}
