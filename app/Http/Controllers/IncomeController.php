<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use Illuminate\Http\JsonResponse;

class IncomeController extends RepositoryController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  IncomeRequest  $request
     * @return IncomeResource
     */
    public function store(IncomeRequest $request): IncomeResource
    {
        return new IncomeResource($this
            ->repository
            ->save($request));
    }

    /**
     * Display the specified resource.
     *
     * @param  Income  $expense
     * @return IncomeResource
     */
    public function show(Income $income): IncomeResource
    {
        return new IncomeResource($this
            ->repository
            ->find($income->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  IncomeRequest  $request
     * @param  Income  $expense
     * @return IncomeResource
     */
    public function update(IncomeRequest $request, Income $income): IncomeResource
    {
        return new IncomeResource($this
            ->repository
            ->save($request, $income));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Income  $expense
     * @return JsonResponse
     */
    public function destroy(Income $income): JsonResponse
    {
        $this
            ->repository
            ->delete($income->id);

        return response()->json(['message' => 'Income deleted']);
    }

    /**
     * @return string
     */
    public static function getResource(): string
    {
        return IncomeResource::class;
    }
}
