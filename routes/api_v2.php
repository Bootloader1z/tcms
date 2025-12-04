<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\TasFileApiController;
use App\Http\Controllers\Api\AdmittedApiController;
use App\Http\Controllers\Api\ArchivesApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group with Sanctum authentication.
|
*/

// Public API routes
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/register', [AuthApiController::class, 'register'])->middleware('throttle:3,1');
});

// Protected API routes
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Authentication
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dashboard & Analytics
    Route::get('/dashboard/stats', [DashboardApiController::class, 'getStats']);
    Route::get('/analytics/chart-data', [DashboardApiController::class, 'getChartData']);
    Route::get('/analytics/pie-chart', [DashboardApiController::class, 'getPieChartData']);
    Route::get('/analytics/violations', [DashboardApiController::class, 'getViolationRankings']);

    // TAS Files
    Route::apiResource('tas-files', TasFileApiController::class);
    Route::post('/tas-files/{id}/remarks', [TasFileApiController::class, 'addRemark']);
    Route::put('/tas-files/{id}/status', [TasFileApiController::class, 'updateStatus']);
    Route::post('/tas-files/{id}/finish', [TasFileApiController::class, 'finishCase']);

    // Admitted Cases
    Route::apiResource('admitted', AdmittedApiController::class);
    Route::post('/admitted/{id}/remarks', [AdmittedApiController::class, 'addRemark']);
    Route::put('/admitted/{id}/status', [AdmittedApiController::class, 'updateStatus']);

    // Archives
    Route::apiResource('archives', ArchivesApiController::class);
    Route::post('/archives/{id}/remarks', [ArchivesApiController::class, 'addRemark']);
    Route::put('/archives/{id}/status', [ArchivesApiController::class, 'updateStatus']);

    // Officers & Violations
    Route::get('/officers', [DashboardApiController::class, 'getOfficers']);
    Route::get('/officers/{department}', [DashboardApiController::class, 'getOfficersByDepartment']);
    Route::get('/violations', [DashboardApiController::class, 'getViolations']);
    Route::get('/departments', [DashboardApiController::class, 'getDepartments']);
});
