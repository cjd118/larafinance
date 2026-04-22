<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
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
    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create($request->validated());
        $transaction->load(['creditAccount', 'debitAccount']);

        return response()->json([
            'transaction' => new TransactionResource($transaction)
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
