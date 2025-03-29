<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new TransactionCollection(Transaction::orderBy('id', 'desc')->with(['creditAccount', 'debitAccount'])->paginate(100));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //todo: move to form request
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|gt:0',
            //todo: add custom validation rule to ensure credit account is a credit account
            'credit_account_id' => 'required|exists:accounts,id',
            //todo: add custom validation rule to ensure debit account is a debit account
            'debit_account_id' => 'required|exists:accounts,id',
        ]);
    
        $transaction = Transaction::create($validated);
    
        return response()->json([
            'transaction' => new TransactionResource($transaction->with(['creditAccount', 'debitAccount']))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new TransactionResource(Transaction::with(['creditAccount', 'debitAccount'])->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //todo
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
    
        return response(null, 200);
    }
}
