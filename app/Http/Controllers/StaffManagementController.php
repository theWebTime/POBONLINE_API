<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffManagement;
use App\Models\OrganizeDepartment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class StaffManagementController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $data = StaffManagement::join('category_management', 'category_management.id', '=', 'staff_management.category_role_id')->where('staff_management.user_id', '=', auth()->user()->id)->select('staff_management.id', 'name', 'phone_number', 'category_management.category_role as category_role')->where(function ($query) use ($request) {
                if ($request->search != null) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                }
            })->orderBy('id', 'DESC')->paginate($request->itemsPerPage ?? 10);
            return $this->sendResponse($data, 'Staff retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    /* public function index(Request $request)
{
    try {
        $data = StaffManagement::leftJoin('category_management', 'category_management.id', '=', 'staff_management.category_role_id')
            ->leftJoin('organize_departments', 'organize_departments.category_management_id', '=', 'category_management.id')
            ->leftJoin('client_functions', 'client_functions.id', '=', 'organize_departments.client_function_id')
            ->where('staff_management.user_id', '=', auth()->user()->id)
            ->select(
                'staff_management.id',
                'staff_management.name',
                'staff_management.phone_number',
                'category_management.category_role as category_role',
                'client_functions.function_time',
                'client_functions.venue'
            )
            ->when($request->search, function ($query) use ($request) {
                $query->where('staff_management.name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('staff_management.id', 'DESC')
            ->paginate($request->itemsPerPage ?? 10);

        return $this->sendResponse($data, 'Staff retrieved successfully.');
    } catch (Exception $e) {
        return $this->sendError('Something went wrong!', $e);
    }
}
 */

    public function store(Request $request)
    {
        //Using Try & Catch For Error Handling
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required|max:70',
                'phone_number' => 'required|max:15|unique:staff_management,phone_number',
                'email' => 'nullable|max:100|unique:staff_management,email',
                'category_role_id' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['name' => $input['name'], 'phone_number' => $input['phone_number'], 'email' => $input['email'], 'user_id' => auth()->user()->id, 'category_role_id' => $input['category_role_id']]);
            StaffManagement::create($updateData);
            return $this->sendResponse([], 'Staff created successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function show($id)
    {
        //Using Try & Catch For Error Handling
        try {
            $data = StaffManagement::join('category_management', 'category_management.id', '=', 'staff_management.category_role_id')->where('staff_management.id', $id)->select('staff_management.id', 'name', 'phone_number', 'email', 'category_role_id')->first();
            return $this->sendResponse($data, 'Staff retrieved successfully.');
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
                'phone_number' => 'required|max:15|unique:staff_management,phone_number,' . $id,
                'email' => 'nullable|max:100|unique:staff_management,email,' . $id,
                'category_role_id' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $updateData = (['name' => $input['name'], 'phone_number' => $input['phone_number'], 'email' => $input['email'], 'user_id' => auth()->user()->id, 'category_role_id' => $input['category_role_id']]);
            StaffManagement::where('id', $id)->update($updateData);
            return $this->sendResponse([], 'Staff updated successfully.');
        } catch (Exception $e) {
            return $e;
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function delete($id)
    {
        //Using Try & Catch For Error Handling
        try {
            DB::table('staff_management')->where('id', $id)->delete();
            return $this->sendResponse([], 'Staff deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('something went wrong!', $e);
        }
    }

    public function getGroupedStaffFunctions(Request $request)
    {
        try {
            $query = DB::table('organize_departments')
                ->leftJoin('staff_management', 'organize_departments.staff_management_id', '=', 'staff_management.id')
                ->leftJoin('client_functions', 'organize_departments.client_function_id', '=', 'client_functions.id')
                ->select(
                    'staff_management.id as staff_id',
                    'staff_management.name as staff_name',
                    'staff_management.phone_number',
                    'client_functions.date',
                    'client_functions.day_label',
                    'client_functions.function_name',
                    'client_functions.function_time',
                    'client_functions.venue'
                );

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('staff_management.name', 'like', '%' . $search . '%');
            }

            // Clone query to use for pagination
            $paginated = (clone $query)
                ->orderBy('client_functions.date', 'asc')
                ->paginate($request->itemsPerPage ?? 10);

            // Group records by staff_id
            $grouped = collect($paginated->items())->groupBy('staff_id')->map(function ($staffRecords) {
                $first = $staffRecords->first();
                return [
                    'staff_id' => $first->staff_id,
                    'staff_name' => $first->staff_name,
                    'phone_number' => $first->phone_number,
                    'functions' => collect($staffRecords)->map(function ($record) {
                        return [
                            'date' => $record->date,
                            'day_label' => $record->day_label,
                            'function_name' => $record->function_name,
                            'function_time' => $record->function_time,
                            'venue' => $record->venue,
                        ];
                    })->values(),
                ];
            })->values();

            // Return paginated response matching Vuetify structure
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $grouped,
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateStaffPDF($staffId)
    {
        try {
            // Get the staff data
            $staff = StaffManagement::findOrFail($staffId);

            // Get the assigned functions for the staff
            $assignedFunctions = OrganizeDepartment::where('staff_management_id', $staffId)
                                                    ->with('clientFunction', 'client') // Make sure clientFunction is loaded
                                                    ->get();

            // Prepare data to pass to the PDF view
            $data = [
                'staff_name' => $staff->name,
                'staff_phone' => $staff->phone_number,
                'assigned_functions' => $assignedFunctions,
                'function_count' => $assignedFunctions->count(),
            ];

            // Load the PDF view
            $pdf = Pdf::loadView('pdfs.staff-details', $data);

            // Define the filename
            $filename = 'staff_details_' . $staffId . '.pdf';

            // Save the PDF to the 'public/staff-pdfs' folder
            $pdf->save(public_path('staff-pdfs/' . $filename));

            // Return the URL of the generated PDF
            return response()->json([
                'success' => true,
                'url' => url('staff-pdfs/' . $filename),
                'phone_number' => $staff->phone_number
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function downloadStaffPDF($staffId)
    {
        try {
            $staff = StaffManagement::findOrFail($staffId);
            $assignedFunctions = OrganizeDepartment::where('staff_management_id', $staffId)
                                                    ->with('clientFunction', 'client')
                                                    ->get();

            $data = [
                'staff_name' => $staff->name,
                'staff_phone' => $staff->phone_number,
                'assigned_functions' => $assignedFunctions,
                'function_count' => $assignedFunctions->count(),
            ];

            $pdf = Pdf::loadView('pdfs.staff-details', $data);

            // Return PDF with headers
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="staff_details_' . $staffId . '.pdf"');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
