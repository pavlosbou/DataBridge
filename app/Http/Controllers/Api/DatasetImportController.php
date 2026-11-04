<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDatasetImportRequest;
use App\Services\CsvAnalysisService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\CsvValidationService;
use Illuminate\Support\Str;
use Throwable;
use RuntimeException;

class DatasetImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @throws Exception
     * @throws Throwable
     */
    public function store(StoreDatasetImportRequest $request, string $datasetId)
    {

        $dataset = $request->user()->datasets()->where('id', $datasetId)->firstOrFail();
        $file = $request->file('file');
        $path = null;
        $workingPath = null;

        try {
            $path = $file->store('imports', 'local');
            if ($path === false) {
                throw new RuntimeException('Unable to store original csv file.');
            }

            $workingPath = 'imports/working/' . Str::uuid() . '.csv';

            if (!Storage::disk('local')->copy($path, $workingPath)){
                throw new RuntimeException('Unable to create working copy');
            };

            $datasetImports = $dataset->imports()->create([
                'original_filename' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'working_storage_path' => $workingPath,
            ]);
            return response()->json($datasetImports, 201);
        }catch (Throwable $exception){
            if (!empty($path)) {
                Storage::disk('local')->delete($path);
            }
            if (!empty($workingPath)) {
                Storage::disk('local')->delete($workingPath);
            }
            throw $exception;
        }

    }

    public function headers(Request $request, string $datasetId, string $importId)
    {
        $dataset = $request->user()->datasets()->where('id', $datasetId)->firstOrFail();
        $import = $dataset->imports()->where('id', $importId)->firstOrFail();
        $storage = Storage::disk('local');
        if (!$storage->exists($import->storage_path)) {
            return response()->json([
                'message' => 'Uploaded csv file not found',
            ],404);
        }
        $absolutePath = $storage->path($import->storage_path);
        $file = fopen($absolutePath, 'r');

        if( $file === false){
            return response()->json([
                'message' => 'Unable to open csv file',
            ],500);
        }

        try {
            $headers = fgetcsv($file);
        } finally {
            fclose($file);
        }

        if ($headers === false || $headers === [null]) {
            return response()->json([
                'message' => 'The csv file is empty or has no readable header row',
            ],422);
        }

        return response()->json([
            'import_id' => $import->id,
            'headers' => $headers,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function analyze(Request $request, string $datasetId, string $importId, CsvAnalysisService $csvAnalysisService)
    {
        $dataset = $request->user()->datasets()->where('id', $datasetId)->firstOrFail();
        $import = $dataset->imports()->where('id', $importId)->firstOrFail();
        $analysis = $csvAnalysisService->analyze($import->storage_path);
        return response()->json([
            'import_id' => $import->id,
             ...$analysis]);
    }

    public function validate(Request $request, string $datasetId, string $importId, CsvValidationService $csvValidationService)
    {
        $dataset = $request->user()->datasets()->where('id', $datasetId)->firstOrFail();
        $import = $dataset->imports()->where('id', $importId)->firstOrFail();
        $fields = $dataset->fields()->get(['name', 'type'])->toArray();


        $validation = $csvValidationService->validateFile(
            $import->storage_path,
            $fields,
        );

        if ($validation['structureErrors']) {
            return response()->json([
                'structureErrors' => $validation['structureErrors'],
                'headers' => $validation['headers'],
                'missingHeaders' => $validation['missingHeaders'],
                'unknownHeaders' => $validation['unknownHeaders'],
                'duplicateHeaders' => $validation['duplicateHeaders'],
            ], 422);
        }
        $status = $validation['invalidRows'] === 0
            ? 'completed'
            : 'completed_with_errors'
        ;

        $import->update([
            'status' => $status,
            'total_rows' => $validation['totalRows'],
            'valid_rows' => $validation['validRows'],
            'invalid_rows' => $validation['invalidRows'],
        ]);

        return response()->json([
            'import_id' => $import->id,
            'status' => $status,
            'total_rows' => $validation['totalRows'],
            'valid_rows' => $validation['validRows'],
            'invalid_rows' => $validation['invalidRows'],
            'validation_errors' => $validation['validationErrors'],
        ]);
    }
}
