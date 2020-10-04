<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LabelController extends RepositoryController
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return LabelResource::collection($this
            ->repository
            ->getItems());
    }

    /**
     * Store a newly created resource in storage.
     * @param  LabelRequest $request
     * @return LabelResource
     */
    public function store(LabelRequest $request): LabelResource
    {
        return new LabelResource($this
            ->repository
            ->save($request));
    }

    /**
     * Display the specified resource.
     *
     * @param  Label  $activity
     * @return LabelResource
     */
    public function show(Label $activity): LabelResource
    {
        return new LabelResource($this
            ->repository
            ->find($activity->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  LabelRequest  $request
     * @param  Label  $activity
     * @return LabelResource
     */
    public function update(LabelRequest $request, Label $activity): LabelResource
    {
        return new LabelResource($this
            ->repository
            ->save($request, $activity));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Label  $expense
     * @return JsonResponse
     */
    public function destroy(Label $activity): JsonResponse
    {
        $this
            ->repository
            ->delete($activity->id);

        return response()->json(['message' => 'Label deleted']);
    }
}
