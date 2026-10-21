<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDatasetRequest;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDatasetRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DatasetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $datasets = $request->user()
            ->datasets()
            ->latest()
            ->paginate(20);
        return response()->json($datasets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDatasetRequest $request)
    {
        $validatedData = $request->validated();
        $dataset = $request->user()->datasets()->create($validatedData);
        return response()->json($dataset,201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {
        $dataset = $request->user()
                            ->datasets()
                            ->where('id', $id)
                            ->firstOrFail();

        return response()->json($dataset);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDatasetRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $dataset = $request->user()
            ->datasets()
            ->where('id', $id)
            ->firstOrFail();


        $dataset->update($validatedData);


        return response()->json($dataset);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        $dataset = $request->user()
            ->datasets()
            ->where('id', $id)
            ->firstOrFail();

        $dataset->delete();

        return response()->noContent();
    }
}
