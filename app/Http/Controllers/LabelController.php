<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use Illuminate\Http\JsonResponse;

class LabelController extends RepositoryController
{
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
    public function show(Label $label): LabelResource
    {
        return new LabelResource($this
            ->repository
            ->find($label->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  LabelRequest  $request
     * @param  Label  $activity
     * @return LabelResource
     */
    public function update(LabelRequest $request, Label $label): LabelResource
    {
        return new LabelResource($this
            ->repository
            ->save($request, $label));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Label  $expense
     * @return JsonResponse
     */
    public function destroy(Label $label): JsonResponse
    {
        $this
            ->repository
            ->delete($label->id);

        return response()->json(['message' => 'Label deleted']);
    }

    /**
     * @return string
     */
    public static function getResource(): string
    {
        return LabelResource::class;
    }
}
