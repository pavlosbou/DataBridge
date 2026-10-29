<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDatasetImportRequest;
use App\Services\CsvAnalysisService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     */
    public function store(StoreDatasetImportRequest $request, string $datasetId)
    {

        $dataset = $request->user()->datasets()->where('id', $datasetId)->firstOrFail();
        $file = $request->file('file');
        $path = $file->store('imports', 'local');

        try {
            $datasetImports = $dataset->imports()->create([
                'original_filename' => $file->getClientOriginalName(),
                'storage_path' => $path,
            ]);
            return response()->json($datasetImports, 201);
        }catch (Exception $exception){
            Storage::disk('local')->delete($path);
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
}
