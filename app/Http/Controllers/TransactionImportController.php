<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\TransactionImporter;
use Illuminate\Http\Request;

class TransactionImportController extends Controller
{
    // public function index()
    // {
    //     return new TransactionCollection(Transaction::orderBy('id', 'desc')->with(['creditAccount', 'debitAccount'])->paginate(100));
    // }

    public function store(Request $request)
    {
        //todo: move to form request
        $validated = $request->validate([
            'name' => 'required|string|exists:transaction_importers,name',
            'data' => 'required',
        ]);

        $transactionImporter = TransactionImporter::where('name', $request->name)->firstOrFail();

        $dynamicTransactionImporter = app()->make($transactionImporter->class_name);

        $dynamicTransactionImporter->validate($request->data);
    
        return response('Transactions imported successfully', 201);
    }
}
