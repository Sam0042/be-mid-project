<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientsController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware(["auth:sanctum"])->group(function(){
    //Get all resource
    Route::get("/patients",[PatientsController::class,"index"]);

    //Add resource
    Route::post("/patients",[PatientsController::class,"store"]);

    //Get detail resource
    Route::get("/patients/{id}",[PatientsController::class,"show"]);

    //Edit resource
    Route::put("/patients/{id}",[PatientsController::class,"update"]);

    //Delete resource
    Route::delete("/patients/{id}",[PatientsController::class,"destroy"]);
});



#AUTH
//Register
Route::post("/register",[AuthController::class,"register"]);

//Login
Route::post("/login",[AuthController::class,"login"]);