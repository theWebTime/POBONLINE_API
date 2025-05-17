<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrganizeDepartment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class OrganizeDepartmentController extends BaseController
{
    public function index(Request $request)
    {
        $organizedStaff = OrganizeDepartment::with([
            'staff:id,name', 
            'category:id,category_role', 
            'client:id,name'
        ])
        ->orderBy('function_date')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $organizedStaff
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'parts' => 'nullable|array',
            'parts.*.categories' => 'nullable|array',
            'parts.*.categories.*.category_management_id' => 'nullable|exists:category_managements,id',
            'parts.*.categories.*.selected_staff_ids' => 'nullable|array|min:1', // staff multiple
            'parts.*.categories.*.selected_staff_ids.*' => 'nullable|exists:staff_management,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['parts'] as $part) {
                foreach ($part['categories'] as $category) {
                    foreach ($category['selected_staff_ids'] as $staffId) {
                        OrganizeDepartment::create([
                            'client_id' => $validated['client_id'],
                            'category_management_id' => $part['category_management_id'],
                            'staff_id' => $staffId,
                            'user_id' => auth()->user->id,
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Organized staff saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save organized staff.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request)
{
    $validated = $request->validate([
        'client_id' => 'nullable|exists:clients,id',
        'functions' => 'nullable|array',
        'functions.*.categories' => 'nullable|array',
        'functions.*.categories.*.category_management_id' => 'nullable|exists:category_managements,id',
        'functions.*.categories.*.selected_staff_ids' => 'nullable|array|min:1',
        'functions.*.categories.*.selected_staff_ids.*' => 'nullable|exists:staff_management,id',
    ]);

    DB::beginTransaction();
    try {
        // Clear existing records for this client and date
        OrganizeDepartment::where('client_id', $validated['client_id'])->delete();

        // Insert the updated records
        foreach ($validated['functions'] as $function) {
            foreach ($function['categories'] as $category) {
                foreach ($category['selected_staff_ids'] as $staffId) {
                    OrganizeDepartment::create([
                        'client_id' => $validated['client_id'],
                        'category_management_id' => $category['category_management_id'],
                        'staff_id' => $staffId,
                    ]);
                }
            }
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Organized staff updated successfully.',
        ]);
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Failed to update organized staff.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
    public function generatePDF()
    {
        $organizedStaff = OrganizeDepartment::with(['staff', 'category', 'client'])
            ->orderBy('function_date', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdfs.organize_department_pdf', compact('organizedStaff'))
                ->setPaper('a4', 'portrait');

        return $pdf->download('organized_staff_list.pdf');
    }

    public function generateStaffPDF($staffId)
    {
        $organizedStaff = OrganizeDepartment::where('staff_id', $staffId)
            ->with(['staff', 'category', 'client']) // Assuming you have relations
            ->orderBy('function_date')
            ->get();

        if ($organizedStaff->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No organized data found for this staff.',
            ], 404);
        }

        $pdf = Pdf::loadView('pdfs.organize_department_pdf', compact('organizedStaff'));

        $staffName = optional($organizedStaff->first()->staff)->name ?? 'Staff';

        return $pdf->download($staffName . '_organized_schedule.pdf');
    }
}
