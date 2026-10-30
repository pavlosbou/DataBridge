<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDatasetSchemaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatasetSchemaController extends Controller
{
    //Later I will define schema versioning or updating only changed fields,
    //now that I don't have entries that matter, it's fine
    public function save(SaveDatasetSchemaRequest $request, string $datasetId)
    {
        $dataset = $request->user()->datasets()->findOrFail($datasetId);
        $fields = $request->validated('fields');
        $created_fields = DB::transaction(function () use ($dataset, $fields) {
            $dataset->fields()->delete();
            foreach ($fields as $field) {
                $dataset->fields()->create($field);
            }
            return $dataset->fields()->get();

        });

        return response()->json([
            'dataset_id' => $dataset->id,
            'fields' => $created_fields,
        ]);
    }
}
