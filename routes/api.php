<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DatasetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/datasets', [DatasetController::class, 'index']);

Route::middleware('auth:sanctum')->post('/datasets', [DatasetController::class, 'store']);

Route::middleware('auth:sanctum')->get('/datasets/{id}', [DatasetController::class, 'show']);

Route::middleware('auth:sanctum')->patch('/datasets/{id}', [DatasetController::class, 'update']);

Route::middleware('auth:sanctum')->delete('/datasets/{id}', [DatasetController::class, 'destroy']);
