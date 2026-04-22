<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionCategoryRequest;
use App\Http\Requests\UpdateTransactionCategoryRequest;
use App\Http\Resources\TransactionCategoryCollection;
use App\Http\Resources\TransactionCategoryResource;
use App\Models\TransactionCategory;

class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new TransactionCategoryCollection(TransactionCategory::all()->sortBy(function ($category) {
            return $category->getPathFormatted();
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionCategoryRequest $request)
    {
        $transactionCategory = TransactionCategory::create($request->validated());
    
        return response()->json([
            'transaction' => new TransactionCategoryResource($transactionCategory)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new TransactionCategoryResource(TransactionCategory::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionCategoryRequest $request, string $id)
    {
        $transactionCategory = TransactionCategory::findOrFail($id);
        $transactionCategory->update($request->validated());
    
        return response()->json([
            'transaction' => new TransactionCategoryResource($transactionCategory)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transactionCategory = TransactionCategory::findOrFail($id);
        $transactionCategory->delete();
    
        return response(null, 200);
    }
}
