<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientFunction;
use App\Models\ManageClientCategory;
use App\Models\CategoryManagement;
use App\Models\GenerateBill;
use App\Models\OrganizeDepartment;
use App\Models\ClientComplimentService;
use App\Models\StaffManagement;
use App\Models\PrivacyPolicy;
use App\Models\ExternalService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
// use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController as BaseController;

class ClientController extends BaseController
{
    /* public function index(Request $request)
    {
        try {
            $data = Client::join('particular_functions', 'particular_functions.id', '=', 'clients.particular_function_id')
                ->where('clients.user_id', '=', auth()->user()->id)
                ->select('clients.id', 'clients.name', 'clients.starting_date', 'particular_functions.name as particular_function_name')
                ->when($request->search, function ($query, $search) {
                    $query->where('clients.name', 'like', '%' . $search . '%');
                })
                ->orderBy('clients.id', 'DESC')
                ->paginate($request->itemsPerPage ?? 10);

            // Format the starting_date
            $data->setCollection(
                $data->getCollection()->map(function ($item) {
                    $item->starting_date = \Carbon\Carbon::parse($item->starting_date)->format('d-m-Y');
                    return $item;
                })
            );

            return $this->sendResponse($data, 'Client retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', $e->getMessage());
        }
    } */

    protected function parseBreakdown($breakdown)
    {
        if (is_array($breakdown)) return $breakdown;
        if (is_string($breakdown)) return json_decode($breakdown, true) ?: [];
        return [];
    }

    public function index(Request $request)
    {
        try {
            $clients = Client::with([
                'particularFunction:id,name',
                'complimentServices:id,name',
                'clientFunctions.categories.categoryManagement:id,category_role',
                'generateBill:id,client_id,discount_percentage,grand_total,breakdown',
                'yourStory:id,user_id,image,image2',
            ])
            ->where('user_id', auth()->id())
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('clients.name', 'like', '%' . $search . '%')
                    ->orWhereHas('particularFunction', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($request->itemsPerPage ?? 10);

            $privacyPolicy = PrivacyPolicy::latest()->first(); // or use user_id if needed
            $externalServices = ExternalService::all();

            $user = Auth::user();
            // Transform each client with all details
            $transformedClients = $clients->getCollection()->map(function ($client) use ($privacyPolicy, $externalServices, $user) {
                return [
                    'id' => $client->id,
                    'image' => $client->yourStory->image ?? null,
                    'image2' => $client->yourStory->image2 ?? null,
                    'name' => $client->name,
                    'phone_number' => $client->phone_number,
                    'address' => $client->address,
                    'is_booked' => $client->is_booked,
                    'starting_date' => \Carbon\Carbon::parse($client->starting_date)->format('d-m-Y'),
                    'particular_function' => $client->particularFunction ? [
                        'id' => $client->particularFunction->id,
                        'name' => $client->particularFunction->name
                    ] : null,
                    'compliment_services' => $client->complimentServices->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name
                        ];
                    }),
                    'privacy_policy' => $privacyPolicy ? $privacyPolicy->privacy_policy : null,
                    'external_services' => $externalServices->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'service_name' => $service->service_name,
                            'service_price' => $service->service_price
                        ];
                    }),
                    'client_functions' => $client->clientFunctions->map(function ($function) {
                        return [
                            'id' => $function->id,
                            'date' => $function->date,
                            'day_label' => $function->day_label,
                            'function_name' => $function->function_name,
                            'categories' => $function->categories->map(function ($category) {
                                return [
                                    'id' => $category->id,
                                    'category_management_id' => $category->category_management_id,
                                    'category_quantity' => $category->category_quantity,
                                    'category_name' => $category->categoryManagement->category_role ?? null
                                ];
                            })
                        ];
                    }),
                    'generate_bill' => $client->generateBill ? [
                    'id' => $client->generateBill->id,
                    'discount_percentage' => $client->generateBill->discount_percentage,
                    'grand_total' => $client->generateBill->grand_total,
                    'breakdown' => $this->parseBreakdown($client->generateBill->breakdown)
                ] : null,

                    'user_details' => [
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'studio_name' => $user->studio_name,
                    'image' => $user->image,
                    'address' => $user->address,
                    'email' => $user->email,
                    'instagram_link' => $user->instagram_link,
                ]
                    // 'invoice_download_url' => route('clients.download-invoice', $client->id)
                ];
            });

            // Create custom paginator with transformed data
            $data = new \Illuminate\Pagination\LengthAwarePaginator(
                $transformedClients,
                $clients->total(),
                $clients->perPage(),
                $clients->currentPage(),
                ['path' => $request->url()]
            );

            return $this->sendResponse($data, 'Clients retrieved successfully.');

        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:70',
                'phone_number' => 'required|max:15',
                'address' => 'required|string',
                'starting_date' => 'required|date',
                'particular_function_id' => 'required|exists:particular_functions,id',
                'compliment_service_ids' => 'nullable|array',
                'compliment_service_ids.*' => 'exists:compliment_services,id',
                'parts' => 'nullable|array',
                'parts.*.date' => 'nullable|date',
                'parts.*.day_label' => 'nullable|string|max:80',
                'parts.*.function_name' => 'nullable|string|max:80',
                'parts.*.categories' => 'nullable|array',
                'parts.*.categories.*.category_management_id' => 'nullable|exists:category_management,id',
                'parts.*.categories.*.category_quantity' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Create client
            $client = Client::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'starting_date' => $request->starting_date,
                'particular_function_id' => $request->particular_function_id,
                'user_id' => auth()->id(),
            ]);

            // Save compliment services
            if ($request->has('compliment_service_ids')) {
                $client->complimentServices()->sync($request->compliment_service_ids);
            }

            $grandTotal = 0;
            $breakdown = [];
            $createdFunctions = [];
            $createdCategories = [];

            if ($request->has('parts')) {
                foreach ($request->parts as $part) {
                    // Create function
                    $clientFunction = ClientFunction::create([
                        'client_id' => $client->id,
                        'date' => $part['date'] ?? null,
                        'day_label' => $part['day_label'] ?? null,
                        'function_name' => $part['function_name'] ?? null,
                        'function_time' => $part['function_time'] ?? null,
                        'venue' => $part['venue'] ?? null,
                        'user_id' => auth()->id(),
                    ]);

                    $createdFunctions[] = $clientFunction;
                    $functionTotal = 0;
                    $functionCategories = [];

                    // Process categories
                    if (!empty($part['categories'])) {
                        foreach ($part['categories'] as $categoryData) {
                            // Get category price
                            $categoryManagement = CategoryManagement::find($categoryData['category_management_id']);
                            $subtotal = $categoryManagement->category_price * $categoryData['category_quantity'];
                            
                            // Create category
                            $category = ManageClientCategory::create([
                                'client_function_id' => $clientFunction->id,
                                'category_management_id' => $categoryData['category_management_id'],
                                'category_quantity' => $categoryData['category_quantity'],
                                'user_id' => auth()->id(),
                            ]);

                            $createdCategories[] = $category;
                            $functionTotal += $subtotal;

                            $functionCategories[] = [
                                'category_role' => $categoryManagement->category_role,
                                'price' => $categoryManagement->category_price,
                                'quantity' => $categoryData['category_quantity'],
                                'subtotal' => $subtotal
                            ];
                        }
                    }

                    $grandTotal += $functionTotal;
                    
                    $breakdown[] = [
                        'function_name' => $part['function_name'] ?? null,
                        'function_time' => $part['function_time'] ?? null,
                        'venue' => $part['venue'] ?? null,
                        'date' => $part['date'] ?? null,
                        'day_label' => $part['day_label'] ?? null,
                        'total' => $functionTotal,
                        'categories' => $functionCategories
                    ];
                }
            }

            // Generate and store the bill
            GenerateBill::create([
                'client_id' => $client->id,
                'grand_total' => $grandTotal,
                'breakdown' => json_encode($breakdown),
                // 'user_id' => auth()->id()
            ]);

            DB::commit();

            return $this->sendResponse([
                'client' => $client->load('complimentServices'),
                'client_functions' => $createdFunctions,
                'categories' => $createdCategories,
                'bill' => [
                    'grand_total' => $grandTotal,
                    'breakdown' => $breakdown
                ]
            ], 'Client data and bill created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Something went wrong!', $e->getMessage());
        }
    }

   
    public function show($id)
    {
        try {
            $client = Client::with([
                'particularFunction:id,name',
                'complimentServices:id,name',
                'generateBill:id,client_id,discount_percentage,grand_total,breakdown',
                'clientFunctions' => function ($q) {
                    $q->select('id', 'client_id', 'function_name', 'function_time', 'venue', 'date', 'day_label', 'user_id');
                },
                'clientFunctions.categories' => function ($q) {
                    $q->select('id', 'client_function_id', 'category_management_id', 'category_quantity', 'user_id')
                        ->with(['categoryManagement:id,category_role']);
                },
            ])->find($id);

            if (!$client) {
                return $this->sendError('Client not found', [], 404);
            }

            // Fetch all related organize_department records
            $organizedDepartments = OrganizeDepartment::with([
                'staffManagement:id,name,phone_number,email,category_role_id',
            ])->where('client_id', $id)->get();

            $organizedByFunctionCategory = [];

            foreach ($organizedDepartments as $org) {
                $functionId = $org->client_function_id;
                $categoryId = $org->manage_client_category_id;

                $organizedByFunctionCategory[$functionId][$categoryId]['time'] = $org->function_time;
                $organizedByFunctionCategory[$functionId][$categoryId]['venue'] = $org->venue;
                $organizedByFunctionCategory[$functionId][$categoryId]['date'] = $org->function_date;
                $organizedByFunctionCategory[$functionId][$categoryId]['staff'][] = [
                    'id' => $org->staffManagement?->id,
                    'name' => $org->staffManagement?->name,
                    'phone_number' => $org->staffManagement?->phone_number,
                    'email' => $org->staffManagement?->email,
                ];
            }

            $response = [
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'phone_number' => $client->phone_number,
                    'address' => $client->address,
                    'is_booked' => $client->is_booked,
                    'starting_date' => $client->starting_date,
                    'particular_function' => $client->particularFunction,
                    'compliment_services' => $client->complimentServices,
                    'generate_bill' => $client->generateBill,
                    'client_functions' => $client->clientFunctions->map(function ($func) use ($organizedByFunctionCategory) {
                        return [
                            'id' => $func->id,
                            'function_name' => $func->function_name,
                            'function_time' => $func->function_time,
                            'venue' => $func->venue,
                            'date' => $func->date,
                            'day_label' => $func->day_label,
                            'categories' => $func->categories->map(function ($cat) use ($func, $organizedByFunctionCategory) {
                                $orgData = $organizedByFunctionCategory[$func->id][$cat->id] ?? [];

                                return [
                                    'id' => $cat->id,
                                    'category_management_id' => $cat->category_management_id,
                                    'category_quantity' => $cat->category_quantity,
                                    'category_role' => $cat->categoryManagement?->category_role,
                                    /* 'time' => $orgData['time'] ?? null,
                                    'venue' => $orgData['venue'] ?? null,
                                    'date' => $orgData['date'] ?? null, */
                                    'selected_staff_ids' => collect($orgData['staff'] ?? [])->pluck('id')->toArray(),
                                    'available_staff' => $orgData['staff'] ?? [],
                                ];
                            })->values(),
                        ];
                    })->values(),
                ]
            ];

            return $this->sendResponse($response, 'Client data retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e->getMessage(), 500);
        }
    }



    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:70',
                'phone_number' => 'required|max:15',
                'address' => 'required|string',
                'starting_date' => 'required|date',
                'particular_function_id' => 'required|exists:particular_functions,id',
                'is_booked' => 'nullable|in:0,1',
                'compliment_service_ids' => 'nullable|array',
                'compliment_service_ids.*' => 'exists:compliment_services,id',
                'parts' => 'required|array',
                'parts.*.function_name' => 'required|string|max:80',
                'parts.*.date' => 'required|date',
                'parts.*.time' => 'nullable',
                'parts.*.venue' => 'nullable|string|max:255',
                'parts.*.categories' => 'nullable|array',
                'parts.*.categories.*.category_management_id' => 'nullable|exists:category_management,id',
                'parts.*.categories.*.category_quantity' => 'nullable|numeric',
                'parts.*.categories.*.selected_staff_ids' => 'nullable|array',
                'parts.*.categories.*.selected_staff_ids.*' => 'exists:staff_management,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Update Client
            $client = Client::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $client->update([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'starting_date' => $request->starting_date,
                'particular_function_id' => $request->particular_function_id,
                'is_booked' => $request->is_booked,
            ]);

            // Sync compliment services
            $client->complimentServices()->sync($request->compliment_service_ids ?? []);

            // Handle deleted functions
            // Handle deleted functions - UPDATED CODE
            if (!empty($request->deleted_function_ids)) {
                // First delete all related categories and their staff assignments
                $categoriesToDelete = ManageClientCategory::whereIn('client_function_id', $request->deleted_function_ids)
                    ->pluck('id');
                    
                if ($categoriesToDelete->isNotEmpty()) {
                    // Delete staff assignments first
                    OrganizeDepartment::whereIn('manage_client_category_id', $categoriesToDelete)->delete();
                    
                    // Then delete the categories
                    ManageClientCategory::whereIn('id', $categoriesToDelete)->delete();
                }
                
                // Now it's safe to delete the functions
                ClientFunction::whereIn('id', $request->deleted_function_ids)->delete();
            }

            // Handle deleted categories
            if (!empty($request->deleted_category_ids)) {
                // First delete staff assignments for these categories
                OrganizeDepartment::whereIn('manage_client_category_id', $request->deleted_category_ids)->delete();
                
                // Then delete the categories
                ManageClientCategory::whereIn('id', $request->deleted_category_ids)->delete();

            }

            // Process functions and categories
            foreach ($request->parts as $part) {
                $clientFunction = ClientFunction::updateOrCreate(
                    ['id' => $part['id'] ?? null, 'client_id' => $client->id],
                    [
                        'function_name' => $part['function_name'],
                        'date' => $part['date'],
                        'day_label' => $part['day_label'],
                        'function_time' => $part['function_time'] ?? null,
                        'venue' => $part['venue'] ?? null,
                        'user_id' => auth()->id(),
                    ]
                );

                foreach ($part['categories'] as $category) {
                    $clientCategory = ManageClientCategory::updateOrCreate(
                        ['id' => $category['id'] ?? null, 'client_function_id' => $clientFunction->id],
                        [
                            'category_management_id' => $category['category_management_id'],
                            'category_quantity' => $category['category_quantity'],
                            'user_id' => auth()->id(),
                        ]
                    );

                    // Sync staff assignments with quantity validation
                    $selectedStaffIds = array_slice(
                        $category['selected_staff_ids'],
                        0,
                        $category['category_quantity'] // Enforce quantity limit
                    );

                    // Get existing assignments
                    $existingAssignments = OrganizeDepartment::where([
                        'client_id' => $client->id,
                        'client_function_id' => $clientFunction->id,
                        'manage_client_category_id' => $clientCategory->id
                    ])->pluck('staff_management_id')->toArray();

                    // Determine changes
                    $toAdd = array_diff($selectedStaffIds, $existingAssignments);
                    $toRemove = array_diff($existingAssignments, $selectedStaffIds);

                    // Remove unselected staff
                    if (!empty($toRemove)) {
                        OrganizeDepartment::where([
                            'client_id' => $client->id,
                            'client_function_id' => $clientFunction->id,
                            'manage_client_category_id' => $clientCategory->id
                        ])->whereIn('staff_management_id', $toRemove)->delete();
                    }

                    // Add new staff
                    foreach ($toAdd as $staffId) {
                        OrganizeDepartment::updateOrCreate(
                            [
                                'client_id' => $client->id,
                                'client_function_id' => $clientFunction->id,
                                'manage_client_category_id' => $clientCategory->id,
                                'staff_management_id' => $staffId
                            ],
                            [
                                'category_management_id' => $category['category_management_id'],
                                'user_id' => auth()->id(),
                            ]
                        );
                    }
                }
            }

            $grandTotal = 0;
            $breakdown = [];
            foreach ($client->clientFunctions()->with('manageClientCategories.categoryManagement')->get() as $function) {
                $functionTotal = 0;
                $categories = [];
                foreach ($function->manageClientCategories as $category) {
                    $subtotal = $category->categoryManagement->category_price * $category->category_quantity;
                    $functionTotal += $subtotal;
                    $categories[] = [
                        'category_role' => $category->categoryManagement->category_role,
                        'price' => $category->categoryManagement->category_price,
                        'quantity' => $category->category_quantity,
                        'subtotal' => $subtotal
                    ];
                }
                $grandTotal += $functionTotal;
                $breakdown[] = [
                    'function_name' => $function->function_name,
                    'date' => $function->date,
                    'day_label' => $function->day_label,
                    'total' => $functionTotal,
                    'categories' => $categories
                ];
            }

            // Recalculate bill (your existing bill calculation logic here)
            GenerateBill::updateOrCreate(
                ['client_id' => $client->id],
                [
                    'grand_total' => $grandTotal,
                    'discount_percentage' => $request->discount_percentage,
                    'breakdown' => $breakdown
                ]
            );

            DB::commit();

            return $this->sendResponse(
                ['client' => $client->load(['complimentServices', 'clientFunctions.categories'])],
                'Client updated successfully with all Team assignments'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Update failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
    
        try {
            $client = Client::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
    
            // Delete all compliment services
            ClientComplimentService::where('client_id', $client->id)->delete();
    
            // Delete all generate bills
            GenerateBill::where('client_id', $client->id)->delete();
    
            // Delete related categories
            $functionIds = $client->clientFunctions()->pluck('id');
            ManageClientCategory::whereIn('client_function_id', $functionIds)->delete();
    
            // Delete client functions
            $client->clientFunctions()->delete();
    
            // Delete client
            $client->delete();
    
            DB::commit();
    
            return $this->sendResponse([], 'Client and all related data deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Something went wrong!', $e->getMessage());
        }
    }    

    public function showSlots($id)
    {
        try {
            // Find the GenerateBill entry by client_id and load related client data
            $generateBill = GenerateBill::with('client:id,name,phone_number') // Eager load client details
                ->where('client_id', $id)
                ->firstOrFail();

            // Get the discount and grand total for calculations
            $discountPercentage = $generateBill->discount_percentage;
            $grandTotal = $generateBill->grand_total;

            // Calculate the discount amount
            $discountAmount = ($discountPercentage / 100) * $grandTotal;

            // Calculate the final total after discount
            $finalTotal = $grandTotal - $discountAmount;

            // Get client details (client_id, name, and phone_number)
            $client = $generateBill->client;

            // Transform the response data
            $responseData = [
                'grand_total' => $grandTotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'final_total' => $finalTotal,  // The discounted total
                'slots' => json_decode($generateBill->slots), // Return slots as an array
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'phone_number' => $client->phone_number
                ]
            ];

            return $this->sendResponse($responseData, 'Slots retrieved successfully.');

        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', $e->getMessage());
        }
    }


    public function updateSlots(Request $request, $id)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'slots' => 'nullable|array',
            'slots.*.slot_name' => 'nullable|string',
            'slots.*.slot_percentage' => 'nullable|numeric',
            'slots.*.payment' => 'nullable|boolean',
            'slots.*.date' => 'nullable|date',
        ]);

        // Find the GenerateBill entry by client_id
        $generateBill = GenerateBill::where('client_id', $id)->firstOrFail();

        // Update the slots data
        $generateBill->update([
            'slots' => json_encode($validated['slots']),
        ]);

        // Return the updated bill with slots
        return $this->sendResponse($generateBill, 'Slots created successfully.');
    }

    public function generateInvoice($clientId)
    {
        try {
            $client = Client::with([
                'particularFunction:id,name',
                'complimentServices:id,name',
                'clientFunctions.categories.categoryManagement:id,category_role',
                'generateBill:id,client_id,discount_percentage,grand_total,breakdown',
                'yourStory:id,user_id,image,image2',
            ])->findOrFail($clientId);

            $privacyPolicy = PrivacyPolicy::latest()->first(); // or use user_id if needed
            $externalServices = ExternalService::all();
            $user = Auth::user();
            // Prepare transformed data exactly like you do in index()
            $transformedClient = [
                'id' => $client->id,
                'image' => $client->yourStory->image ?? null,
                'image2' => $client->yourStory->image2 ?? null,
                'name' => $client->name,
                'phone_number' => $client->phone_number,
                'address' => $client->address,
                'starting_date' => \Carbon\Carbon::parse($client->starting_date)->format('d-m-Y'),
                'particular_function' => $client->particularFunction ? [
                    'id' => $client->particularFunction->id,
                    'name' => $client->particularFunction->name
                ] : null,
                'compliment_services' => $client->complimentServices->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name
                    ];
                }),
                'privacy_policy' => $privacyPolicy ? $privacyPolicy->privacy_policy : null,
                'external_services' => $externalServices->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'service_name' => $service->service_name,
                        'service_price' => $service->service_price
                    ];
                }),
                'client_functions' => $client->clientFunctions->map(function ($function) {
                    return [
                        'id' => $function->id,
                        'date' => $function->date,
                        'day_label' => $function->day_label,
                        'function_name' => $function->function_name,
                        'categories' => $function->categories->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'category_management_id' => $category->category_management_id,
                                'category_quantity' => $category->category_quantity,
                                'category_name' => $category->categoryManagement->category_role ?? null
                            ];
                        })
                    ];
                }),
                'generate_bill' => $client->generateBill ? [
                    'id' => $client->generateBill->id,
                    'discount_percentage' => $client->generateBill->discount_percentage,
                    'grand_total' => $client->generateBill->grand_total,
                    'breakdown' => $this->parseBreakdown($client->generateBill->breakdown)
                ] : null,

                'user_details' => [
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'studio_name' => $user->studio_name,
                'image' => $user->image,
                'address' => $user->address,
                'email' => $user->email,
                'instagram_link' => $user->instagram_link,
                'facebook_link' => $user->facebook_link,
                'youtube_link' => $user->youtube_link,
                'website_link' => $user->website_link,
                ]
            ];

            // Pass this transformed client to the PDF view
            $pdf = Pdf::loadView('pdfs.invoice-template', ['client' => $transformedClient]);

            // Now store it manually using public_path
            $path = public_path('quotation_hub/file/brochure');
            if (!file_exists($path)) {
                mkdir($path, 0777, true); // create folder if not exists
            }

            $filename = 'invoice_' . $client->id . '_' . time() . '.pdf';
            $fullPath = $path . '/' . $filename;

            file_put_contents($fullPath, $pdf->output());

            $url = asset('quotation_hub/file/brochure/' . $filename);

            return response()->json([
                'success' => true,
                'url' => $url,
                'phone_number' => $client->phone_number,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadInvoice($clientId)
    {
        try {
            // Generate the PDF fresh each time (don't check for existing file)
            $client = Client::with([
                'particularFunction:id,name',
                'complimentServices:id,name',
                'clientFunctions.categories.categoryManagement:id,category_role',
                'generateBill:id,client_id,discount_percentage,grand_total,breakdown',
                'yourStory:id,user_id,image,image2',
            ])->findOrFail($clientId);

            $privacyPolicy = PrivacyPolicy::latest()->first();
            $externalServices = ExternalService::all();
            $user = Auth::user();
            
            // Your existing transformation logic
            $transformedClient = [
                'id' => $client->id,
                'image' => $client->yourStory->image ?? null,
                'image2' => $client->yourStory->image2 ?? null,
                'name' => $client->name,
                'phone_number' => $client->phone_number,
                'address' => $client->address,
                'starting_date' => \Carbon\Carbon::parse($client->starting_date)->format('d-m-Y'),
                'particular_function' => $client->particularFunction ? [
                    'id' => $client->particularFunction->id,
                    'name' => $client->particularFunction->name
                ] : null,
                'compliment_services' => $client->complimentServices->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name
                    ];
                }),
                'privacy_policy' => $privacyPolicy ? $privacyPolicy->privacy_policy : null,
                'external_services' => $externalServices->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'service_name' => $service->service_name,
                        'service_price' => $service->service_price
                    ];
                }),
                'client_functions' => $client->clientFunctions->map(function ($function) {
                    return [
                        'id' => $function->id,
                        'date' => $function->date,
                        'day_label' => $function->day_label,
                        'function_name' => $function->function_name,
                        'categories' => $function->categories->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'category_management_id' => $category->category_management_id,
                                'category_quantity' => $category->category_quantity,
                                'category_name' => $category->categoryManagement->category_role ?? null
                            ];
                        })
                    ];
                }),
                'generate_bill' => $client->generateBill ? [
                    'id' => $client->generateBill->id,
                    'discount_percentage' => $client->generateBill->discount_percentage,
                    'grand_total' => $client->generateBill->grand_total,
                    'breakdown' => $this->parseBreakdown($client->generateBill->breakdown)
                ] : null,

                'user_details' => [
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'studio_name' => $user->studio_name,
                'image' => $user->image,
                'address' => $user->address,
                'email' => $user->email,
                'instagram_link' => $user->instagram_link,
                'facebook_link' => $user->facebook_link,
                'youtube_link' => $user->youtube_link,
                'website_link' => $user->website_link,
                ]
            ];

            $pdf = Pdf::loadView('pdfs.invoice-template', ['client' => $transformedClient]);
            
            // Download directly without saving to disk
            return $pdf->download('invoice_'.$clientId.'.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating/downloading invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /* public function downloadInvoice($clientId)
    {
        try {
            $filename = 'invoice_' . $clientId . '_' . time() . '.pdf';
            $filePath = public_path('quotation_hub/file/brochure/' . $filename);
            
            // Generate the PDF if it doesn't exist
            if (!file_exists($filePath)) {
                $this->generateInvoice($clientId);
                // Wait a moment for the file to be created
                sleep(1);
            }
            
            return response()->download($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    } */
    public function generateReceipt($clientId)
    {
        try {
            // Find the GenerateBill entry by client_id
            $generateBill = GenerateBill::where('client_id', $clientId)->firstOrFail();

            // Fetch client details
            $client = Client::findOrFail($clientId);

            // Calculate totals
            $discountPercentage = $generateBill->discount_percentage;
            $grandTotal = $generateBill->grand_total;
            $discountAmount = ($discountPercentage / 100) * $grandTotal;
            $finalTotal = $grandTotal - $discountAmount;

            // Decode the slots
            $slots = json_decode($generateBill->slots);

            // Prepare data for PDF
            $data = [
                'grand_total' => $grandTotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'final_total' => $finalTotal,
                'slots' => $slots,
                'client_name' => $client->name,
                'client_phone' => $client->phone_number,
                'client_address' => $client->address,
            ];

            // Load the PDF view
            $pdf = Pdf::loadView('pdfs.receipt', $data);

            // For WhatsApp sharing: save to disk and return URL
            $filename = 'receipt_' . $clientId . '_' . time() . '.pdf';
            $path = public_path('quotation_hub/receipts');
            
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $pdf->save($path . '/' . $filename);

            return response()->json([
                'success' => true,
                'url' => url('quotation_hub/receipts/' . $filename),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadReceipt($clientId)
    {
        try {
            // Generate fresh receipt data each time
            $generateBill = GenerateBill::where('client_id', $clientId)->firstOrFail();
            $client = Client::findOrFail($clientId);

            // Calculate totals
            $discountPercentage = $generateBill->discount_percentage;
            $grandTotal = $generateBill->grand_total;
            $discountAmount = ($discountPercentage / 100) * $grandTotal;
            $finalTotal = $grandTotal - $discountAmount;

            // Decode the slots
            $slots = json_decode($generateBill->slots);

            // Prepare data for PDF
            $data = [
                'grand_total' => $grandTotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'final_total' => $finalTotal,
                'slots' => $slots,
                'client_name' => $client->name,
                'client_phone' => $client->phone_number,
                'client_address' => $client->address,
            ];

            $pdf = Pdf::loadView('pdfs.receipt', $data);
            
            // Download directly without saving to disk
            return $pdf->download('receipt_'.$clientId.'_'.time().'.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating/downloading receipt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /* public function generateReceipt($clientId)
    {
        // Find the GenerateBill entry by client_id
        $generateBill = GenerateBill::where('client_id', $clientId)->firstOrFail();

        if (!$generateBill) {
            return response()->json(['message' => 'Generate Bill not found.'], 404);
        }

        // Fetch client details
        $client = Client::findOrFail($clientId);

        // Calculate totals as before
        $discountPercentage = $generateBill->discount_percentage;
        $grandTotal = $generateBill->grand_total;
        $discountAmount = ($discountPercentage / 100) * $grandTotal;
        $finalTotal = $grandTotal - $discountAmount;

        // Decode the slots
        $slots = json_decode($generateBill->slots);

        // Prepare data to pass to the PDF view, including client details
        $data = [
            'grand_total' => $grandTotal,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_total' => $finalTotal,
            'slots' => $slots,
            'client_name' => $client->name,           // Client name
            'client_phone' => $client->phone_number,  // Client phone number
            'client_address' => $client->address,     // Client address (make sure this exists in your Client model)
        ];

        // Load the PDF view
        $pdf = Pdf::loadView('pdfs.receipt', $data);

        // Define the filename
        $filename = 'receipt_' . $clientId . '.pdf';

        // Save the PDF to the 'public/receipts' folder
        $pdf->save(public_path('quotation_hub/receipts/' . $filename));

        // Return the URL of the generated PDF
        return response()->json([
            'success' => true,
            'url' => url('receipts/' . $filename),
        ]);
    }

    public function downloadReceipt($clientId)
    {
        try {
            $filename = 'receipt_' . $clientId . '.pdf';
            $filePath = public_path('quotation_hub/receipts/' . $filename);
            
            // Generate the PDF if it doesn't exist
            if (!file_exists($filePath)) {
                $this->generateReceipt($clientId);
                // Wait a moment for the file to be created
                sleep(1);
            }
            
            return response()->download($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading receipt',
                'error' => $e->getMessage(),
            ], 500);
        }
    } */

    public function updatePaymentStatus($clientId, $slotId, Request $request)
    {
        // Find the slot and update the payment status
        $slot = Slot::where('client_id', $clientId)->where('id', $slotId)->firstOrFail();
        $slot->payment = $request->payment; // Update the payment status
        $slot->save();

        // Return updated slot with payment status
        return response()->json(['slot' => $slot]);
    }

}
