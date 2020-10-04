<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends RepositoryController
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ExpenseResource::collection($this
            ->repository
            ->getItems());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ExpenseRequest  $request
     * @return ExpenseResource
     */
    public function store(ExpenseRequest $request): ExpenseResource
    {
        return new ExpenseResource($this
            ->repository
            ->save($request));
    }

    /**
     * Display the specified resource.
     *
     * @param  Expense  $expense
     * @return ExpenseResource
     */
    public function show(Expense $expense): ExpenseResource
    {
        return new ExpenseResource($this
            ->repository
            ->find($expense->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ExpenseRequest  $request
     * @param  Expense  $expense
     * @return ExpenseResource
     */
    public function update(ExpenseRequest $request, Expense $expense): ExpenseResource
    {
        return new ExpenseResource($this
            ->repository
            ->save($request, $expense));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Expense  $expense
     * @return JsonResponse
     */
    public function destroy(Expense $expense): JsonResponse
    {
        $this
            ->repository
            ->delete($expense->id);

        return response()->json(['message' => 'Expense deleted']);
    }
}
