<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDatasetFieldRequest;
use App\Http\Requests\UpdateDatasetFieldRequest;
use Illuminate\Http\Request;

class DatasetFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $id)
    {
        $dataset = $request->user()->datasets()->findOrFail($id);
        $fields = $dataset->fields;
        return response()->json($fields);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDatasetFieldRequest $request,string $id)
    {
        $validatedData = $request->validated();
        $dataset = $request->user()->datasets()->where('id', $id)->firstOrFail();
        $field = $dataset->fields()->create($validatedData);
        return response()->json($field, 201);
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
    public function update(UpdateDatasetFieldRequest $request, string $datasetId, string $datasetFieldId )
    {
        $validatedData = $request->validated();
        $dataset = $request->user()->datasets()->where('id', $datasetId)->firstOrFail();
        $field = $dataset->fields()->findOrFail($datasetFieldId);
        $field->update($validatedData);
        return response()->json($field);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $datasetId, string $datasetFieldId)
    {
        $dataset = $request->user()->datasets()->where('id', $datasetId)->firstOrFail();
        $field = $dataset->fields()->findOrFail($datasetFieldId);
        $field->delete();
        return response()->noContent();
    }
}
