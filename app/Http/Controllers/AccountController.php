<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountCollection;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new AccountCollection(Account::orderBy('id', 'desc')->with('category')->paginate(100));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //todo: move to form request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_category_id' => 'required|exists:account_categories,id',
        ]);

        $account = Account::create($validated);

        return response()->json([
            'account' => new AccountResource($account)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new AccountResource(Account::with('category')->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //todo: move to form request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'account_category_id' => 'sometimes|required|exists:account_categories,id',
        ]);
    
        $account = Account::findOrFail($id);
        $account->update($validated);
    
        return response()->json([
            'account' => new AccountResource($account->with('category'))
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $account = Account::findOrFail($id);
        $account->delete();
    
        return response(null, 200);
    }
}
