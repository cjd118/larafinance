<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountCollection;
use App\Http\Resources\AccountResource;
use App\Models\Account;

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
    public function store(StoreAccountRequest $request)
    {
        $account = Account::create($request->validated());

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
    public function update(UpdateAccountRequest $request, string $id)
    {
        $account = Account::findOrFail($id);
        $account->update($request->validated());
        $account->load('category');

        return response()->json([
            'account' => new AccountResource($account)
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
