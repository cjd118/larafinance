<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionImportRequest;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionImport;
use App\Models\TransactionImporter;
use Illuminate\Support\Facades\DB;

class TransactionImportController extends Controller
{
    public function store(StoreTransactionImportRequest $request)
    {
        $transactionImporter = TransactionImporter::where('name', $request->name)->firstOrFail();

        $dynamicTransactionImporter = app()->make($transactionImporter->class_name);

        $dynamicTransactionImporter->loadData($request->data);

        try {
            $dynamicTransactionImporter->validate();
        } catch (\Exception $e) {
            return response('Invalid file format: ' . $e->getMessage(), 422);
        }

        $fingerprint = $dynamicTransactionImporter->generateFingerprint();

        if (TransactionImport::where('fingerprint', $fingerprint)->exists()) {
            return response('This file has already been imported', 409);
        }

        $parsedTransactions = $dynamicTransactionImporter->parse();

        $unassignedIncomeId = Account::where('name', 'Unassigned Income')->value('id');
        $unassignedExpenseId = Account::where('name', 'Unassigned Expense')->value('id');

        $transactionImport = DB::transaction(function () use ($parsedTransactions, $fingerprint, $transactionImporter, $request, $unassignedIncomeId, $unassignedExpenseId) {
            $transactionImport = TransactionImport::create([
                'fingerprint' => $fingerprint,
                'transaction_importer_id' => $transactionImporter->id,
            ]);

            foreach ($parsedTransactions as $parsed) {
                if ($parsed['amount'] >= 0) {
                    $debitAccountId = $request->account_id;
                    $creditAccountId = $unassignedIncomeId;
                } else {
                    $debitAccountId = $unassignedExpenseId;
                    $creditAccountId = $request->account_id;
                }

                Transaction::create([
                    'description' => $parsed['description'],
                    'amount' => abs($parsed['amount']),
                    'credit_account_id' => $creditAccountId,
                    'debit_account_id' => $debitAccountId,
                    'transaction_import_id' => $transactionImport->id,
                ]);
            }

            return $transactionImport;
        });

        return response()->json([
            'transaction_import_id' => $transactionImport->id,
            'imported_count' => count($parsedTransactions),
        ], 201);
    }
}
