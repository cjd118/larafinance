<?php

namespace App\Http\Controllers;

use App\Actions\CreateTransaction;
use App\Http\Requests\StoreTransactionImportRequest;
use App\Models\Account;
use App\Models\TransactionImport;
use App\Models\TransactionImporter;
use App\TransactionImporters\InvalidImportFormat;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class TransactionImportController extends Controller
{
    public function store(StoreTransactionImportRequest $request, CreateTransaction $createTransaction)
    {
        $transactionImporter = TransactionImporter::where('name', $request->name)->firstOrFail();

        $dynamicTransactionImporter = app()->make($transactionImporter->class_name);

        $dynamicTransactionImporter->loadData($request->file('file')->get());

        try {
            $dynamicTransactionImporter->validate();
        } catch (InvalidImportFormat $e) {
            return response('Invalid file format: ' . $e->getMessage(), 422);
        }

        $fingerprint = $dynamicTransactionImporter->generateFingerprint();

        $parsedTransactions = $dynamicTransactionImporter->parse();

        $unassignedIncomeId = Account::where('name', 'Unassigned Income')->value('id');
        $unassignedExpenseId = Account::where('name', 'Unassigned Expense')->value('id');

        try {
            $transactionImport = DB::transaction(function () use ($parsedTransactions, $fingerprint, $transactionImporter, $request, $unassignedIncomeId, $unassignedExpenseId, $createTransaction) {
                $transactionImport = new TransactionImport();
                $transactionImport->fingerprint = $fingerprint;
                $transactionImport->transaction_importer_id = $transactionImporter->id;
                $transactionImport->save();

                foreach ($parsedTransactions as $parsed) {
                    if ($parsed['amount'] >= 0) {
                        $debitAccountId = $request->account_id;
                        $creditAccountId = $unassignedIncomeId;
                    } else {
                        $debitAccountId = $unassignedExpenseId;
                        $creditAccountId = $request->account_id;
                    }

                    $createTransaction->execute([
                        'description' => $parsed['description'],
                        'amount' => abs($parsed['amount']),
                        'credit_account_id' => $creditAccountId,
                        'debit_account_id' => $debitAccountId,
                    ], transactionImportId: $transactionImport->id);
                }

                return $transactionImport;
            });
        } catch (UniqueConstraintViolationException $e) {
            return response('This file has already been imported', 409);
        }

        return response()->json([
            'transaction_import_id' => $transactionImport->id,
            'imported_count' => count($parsedTransactions),
        ], 201);
    }
}
