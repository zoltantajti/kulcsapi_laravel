<?php

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/status', function() { return response()->json(["status" => "ok"]); });
Route::any('/teszt', function(Request $request) {
    $authHeader = $request->header('Authorization');
    return response()->json([
        'msg' => 'Végpont',
        'auth' => $authHeader,
        'fejlec' => $request->headers->all()
    ]);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request){
        return $request->user();
    });
    Route::post('/validate', [LicenseController::class, 'validateKey']);
    Route::post('/use', [LicenseController::class, 'useKey']);
});