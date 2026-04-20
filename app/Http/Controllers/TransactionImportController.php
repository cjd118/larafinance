<?php

namespace App\Http\Controllers;

use App\Models\TransactionImport;
use App\Models\TransactionImporter;
use Illuminate\Http\Request;

class TransactionImportController extends Controller
{
    public function store(Request $request)
    {
        //todo: move to form request
        $validated = $request->validate([
            'name' => 'required|string|exists:transaction_importers,name',
            'data' => 'required',
        ]);

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

        //todo: call $dynamicTransactionImporter->import() and persist results

        TransactionImport::create([
            'fingerprint' => $fingerprint,
            'transaction_importer_id' => $transactionImporter->id,
        ]);

        return response('Transactions imported successfully', 201);
    }
}
