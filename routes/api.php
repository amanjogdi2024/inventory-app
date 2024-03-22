<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FabricController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']); 
    Route::get('/employee_position', [EmployeeController::class, 'showPosition']);

    Route::get('/fabrics', [FabricController::class, 'index']);
    Route::get('/fabrics/{id}', [FabricController::class, 'show']);

    Route::get('/getbarcode/{id}/{poid}', [FabricController::class, 'barCode']);

    Route::post('/getorder', [FabricController::class, 'getOrder']);

    // In your routes/web.php file
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/edit-profile', [AuthController::class, 'updateProfile']);

    Route::get('/packing-list/{pac_id}', [FabricController::class, 'packingList']);
    Route::get('/material-request-list', [FabricController::class, 'materialList']);
    Route::get('/get-material/{fab_id}/{ord_id}', [FabricController::class, 'materialByfabord']); 
    
    Route::get('/material-request/{rq_id}', [FabricController::class, 'materialRequest']); 
});

Route::middleware('geo_location')->get('/geoposition', [EmployeeController::class, 'geoposition']);


Route::post('/login', [AuthController::class, 'login']);

Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
