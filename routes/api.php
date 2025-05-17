<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryManagementController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\ParticularFunctionController;
use App\Http\Controllers\ComplimentServiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrganizeDepartmentController;
use App\Http\Controllers\ExternalServiceController;
use App\Http\Controllers\YourStoryController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\BookDemoController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('register-store', [UserController::class, 'registerStore']);
Route::post('/booking-store', [BookDemoController::class, 'store']);

// Route::get('/clients/{id}/calculate-total', [ClientController::class, 'calculateTotal']);

Route::group(['middleware' => ['auth:api']],  function () {
    Route::post('logout', [AuthController::class, 'logout']);
    // Profile Routes
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile_update', [AuthController::class, 'profile_update']);

    Route::get('/dashboard-data', [ListController::class, 'dashboardData']);
});

Route::group(['middleware' => ['auth:api', 'checkRole:superAdmin']],  function () {
    // User Management Routes
    Route::group(['prefix' => '/user'], function () {
        Route::get('/index', [UserController::class, 'index']);
        Route::post('/store', [UserController::class, 'store']);
        Route::get('/show/{id}', [UserController::class, 'show']);
        Route::post('/update/{id}', [UserController::class, 'update']);
        Route::post('/delete/{id}', [UserController::class, 'delete']);
    });

    // Booking Demo Management Routes
    Route::group(['prefix' => '/booking-demo'], function () {
        Route::get('/index', [BookDemoController::class, 'index']);
        Route::get('/show/{id}', [BookDemoController::class, 'show']);
        Route::post('/update/{id}', [BookDemoController::class, 'update']);
        Route::post('/delete/{id}', [BookDemoController::class, 'delete']);
    });

    // Dashboard Route
    // Route::get('/dashboard-data', [ListController::class, 'dashboardData']);
});

Route::group(['middleware' => ['auth:api', 'checkRole:admin']],  function () {
    // List Routes
    Route::get('/category-index', [ListController::class, 'categoryList']);
    Route::get('/particular-function-index', [ListController::class, 'particularFunctionList']);
    Route::get('/compliment-service-index', [ListController::class, 'complimentServiceList']);
    //Route::get('/staff-by-category/{categoryId}', [ListController::class, 'getStaffByCategory']);
    Route::get('/available-staff/{categoryId}/{clientFunctionId}', [ListController::class, 'getAvailableStaff']);


    // Category Management Routes
    Route::group(['prefix' => '/category'], function () {
        Route::get('/index', [CategoryManagementController::class, 'index']);
        Route::post('/store', [CategoryManagementController::class, 'store']);
        Route::get('/show/{id}', [CategoryManagementController::class, 'show']);
        Route::post('/update/{id}', [CategoryManagementController::class, 'update']);
        Route::post('/delete/{id}', [CategoryManagementController::class, 'delete']);
    });

    // Staff Management Routes
    Route::group(['prefix' => '/staff'], function () {
        Route::get('/index', [StaffManagementController::class, 'index']);
        Route::post('/store', [StaffManagementController::class, 'store']);
        Route::get('/show/{id}', [StaffManagementController::class, 'show']);
        Route::post('/update/{id}', [StaffManagementController::class, 'update']);
        Route::post('/delete/{id}', [StaffManagementController::class, 'delete']);
        // organize staff list Route
        Route::get('/staff-functions-grouped', [StaffManagementController::class, 'getGroupedStaffFunctions']);
    });

    // Particular Function Management Routes
    Route::group(['prefix' => '/particular-function'], function () {
        Route::get('/index', [ParticularFunctionController::class, 'index']);
        Route::post('/store', [ParticularFunctionController::class, 'store']);
        Route::get('/show/{id}', [ParticularFunctionController::class, 'show']);
        Route::post('/update/{id}', [ParticularFunctionController::class, 'update']);
        Route::post('/delete/{id}', [ParticularFunctionController::class, 'delete']);
    });

    // Compliment Service Management Routes
    Route::group(['prefix' => '/compliment-service'], function () {
        Route::get('/index', [ComplimentServiceController::class, 'index']);
        Route::post('/store', [ComplimentServiceController::class, 'store']);
        Route::get('/show/{id}', [ComplimentServiceController::class, 'show']);
        Route::post('/update/{id}', [ComplimentServiceController::class, 'update']);
        Route::post('/delete/{id}', [ComplimentServiceController::class, 'delete']);
    });

    // Cient / Client Function / Manage Client Category Routes
    Route::group(['prefix' => '/client'], function () {
        Route::get('/index', [ClientController::class, 'index']);
        Route::post('/store', [ClientController::class, 'store']);
        Route::get('/show/{id}', [ClientController::class, 'show']);
        Route::post('/update/{id}', [ClientController::class, 'update']);
        Route::delete('/delete/{id}', [ClientController::class, 'delete']);
    });

    // Slots Route
    Route::get('/generate-bill/slots-show/{id}', [ClientController::class, 'showSlots']);
    Route::post('/generate-bill/slots-update/{id}', [ClientController::class, 'updateSlots']);
    Route::post('slots/{clientId}/{slotId}/update-payment', [ClientController::class, 'updatePaymentStatus']);

    // Generate Invoice Route
    Route::get('/clients/generate-invoice/{clientId}', [ClientController::class, 'generateInvoice']);
    // Download Invoice Route
    Route::get('/clients/download-invoice/{clientId}', [ClientController::class, 'downloadInvoice']);
    // Generate Receipt Route
    Route::get('/clients/generate-receipt/{clientId}', [ClientController::class, 'generateReceipt']);
    // Download Receipt Route
    Route::get('/clients/download-receipt/{clientId}', [ClientController::class, 'downloadReceipt']);
    //Generate Staff assigned Route
    Route::get('/staff/generate-pdf/{staffId}', [StaffManagementController::class, 'generateStaffPDF']);
    //Download Staff assigned PDF Route
    Route::get('/staff/download-pdf/{staffId}', [StaffManagementController::class, 'downloadStaffPDF']);


    // Organize Staff Route
    Route::get('/organize-department/index', [OrganizeDepartmentController::class, 'index']);
    Route::post('/organize-department', [OrganizeDepartmentController::class, 'store']);
    Route::post('/organize-department/update/{id}', [OrganizeDepartmentController::class, 'edit']);

    // Organize Staff Generate PDF Route
    Route::get('/organize-department/generate-pdf', [OrganizeDepartmentController::class, 'generatePDF']);

    // Organize Department Generat PDF Per Staff
    //Route::get('/organize-department/generate-staff-pdf/{staffId}', [OrganizeDepartmentController::class, 'generateStaffPDF']);

    // External Service Routes
    Route::group(['prefix' => '/external-service'], function () {
        Route::get('/index', [ExternalServiceController::class, 'index']);
        Route::post('/store', [ExternalServiceController::class, 'store']);
        Route::get('/show/{id}', [ExternalServiceController::class, 'show']);
        Route::post('/update/{id}', [ExternalServiceController::class, 'update']);
        Route::post('/delete/{id}', [ExternalServiceController::class, 'delete']);
    });

    // Your Story Routes
    Route::group(['prefix' => '/your-story'], function () {
        Route::get('/show', [YourStoryController::class, 'show']);
        Route::post('/update', [YourStoryController::class, 'updateOrCreate']);
    });

    // Privacy Policy Routes
    Route::group(['prefix' => '/privacy-policy'], function () {
        Route::get('/show', [PrivacyPolicyController::class, 'show']);
        Route::post('/update', [PrivacyPolicyController::class, 'updateOrCreate']);
    });

    // Total Function Route
    // Route::get('/function-wise-order-count', [ListController::class, 'functionWiseOrderCount']);
    // Route::get('/dashboard-data', [ListController::class, 'dashboardData']);
});