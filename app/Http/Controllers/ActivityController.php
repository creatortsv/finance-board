<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends RepositoryController
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ActivityResource::collection($this
            ->repository
            ->getItems());
    }

    /**
     * Store a newly created resource in storage.
     * @param  ActivityRequest $request
     * @return ActivityResource
     */
    public function store(ActivityRequest $request): ActivityResource
    {
        return new ActivityResource($this
            ->repository
            ->save($request));
    }

    /**
     * Display the specified resource.
     *
     * @param  Activity  $activity
     * @return ActivityResource
     */
    public function show(Activity $activity): ActivityResource
    {
        return new ActivityResource($this
            ->repository
            ->find($activity->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ActivityRequest  $request
     * @param  Activity  $activity
     * @return ActivityResource
     */
    public function update(ActivityRequest $request, Activity $activity): ActivityResource
    {
        return new ActivityResource($this
            ->repository
            ->save($request, $activity));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Activity  $expense
     * @return JsonResponse
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $this
            ->repository
            ->delete($activity->id);

        return response()->json(['message' => 'Activity deleted']);
    }
}
