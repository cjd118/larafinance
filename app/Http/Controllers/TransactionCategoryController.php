<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionCategoryCollection;
use App\Http\Resources\TransactionCategoryResource;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;

class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new TransactionCategoryCollection(TransactionCategory::all()->sortBy(function ($category) {
            return $category->getPath();
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //todo: move to form request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'required', 
                'exists:transaction_categories,id',
            ]
        ]);
    
        $transactionCategory = TransactionCategory::create($validated);
    
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
    public function update(Request $request, string $id)
    {
        //todo: move to form request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'required', 
                'exists:transaction_categories,id',
                //check that parent is not child of itself
                function ($attribute, $value, $fail) use($id) {
                    //get the new parent_id's path
                    $currentPath = TransactionCategory::find($value)->getPath();
                    //is the current id in that path already?
                    foreach($currentPath as $pathElement) {
                        if ($pathElement->id == $id) {
                            return $fail('Parent cannot be child of itself');
                        }
                    }
                }
            ]
        ]);
    
        $transactionCategory = TransactionCategory::findOrFail($id);
        $transactionCategory->update($validated);
    
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
