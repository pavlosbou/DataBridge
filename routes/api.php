<?php

use App\Http\Controllers\Api\DatasetFieldController;
use App\Http\Controllers\Api\DatasetImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DatasetController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Datasets
    Route::get('/datasets', [DatasetController::class, 'index']);
    Route::post('/datasets', [DatasetController::class, 'store']);
    Route::get('/datasets/{id}', [DatasetController::class, 'show']);
    Route::patch('/datasets/{id}', [DatasetController::class, 'update']);
    Route::delete('/datasets/{id}', [DatasetController::class, 'destroy']);

    //DatasetFields
    Route::post('/datasets/{id}/fields', [DatasetFieldController::class, 'store']);
    Route::get('/datasets/{id}/fields', [DatasetFieldController::class, 'index']);
    Route::patch('/datasets/{datasetId}/fields/{datasetFieldId}', [DatasetFieldController::class, 'update']);
    Route::delete('/datasets/{datasetId}/fields/{fieldId}', [DatasetFieldController::class, 'destroy']);

    //DatasetImports
    Route::post('/datasets/{datasetId}/imports', [DatasetImportController::class, 'store']);
    Route::get('/datasets/{datasetId}/imports/{importId}/headers', [DatasetImportController::class, 'headers']);
});
