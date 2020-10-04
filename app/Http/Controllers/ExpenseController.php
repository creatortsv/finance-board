<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Repositories\RepositoryInterface;
use Creatortsv\EloquentPipelinesModifier\ModifierFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends RepositoryController
{
    public function __construct(RepositoryInterface $repository)
    {
        parent::__construct($repository);

        $this->middleware('can:update,expense')->only('update');
        $this->middleware('can:delete,expense')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return ExpenseResource::collection(ModifierFactory::modifyTo($request
            ->user('api')
            ->expenses())
            ->get());
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
        return new ExpenseResource(Expense::modify()->find($expense->id));
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
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();
        return response()->json(['message' => 'Expense deleted']);
    }
}
