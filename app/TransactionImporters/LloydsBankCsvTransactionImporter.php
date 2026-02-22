<?php

namespace App\TransactionImporters;

use League\Csv\Reader;

class LloydsBankCsvTransactionImporter implements TransactionImporter
{
    public function validate(string $data) : bool
    {   
        $reader = Reader::createFromString($data);
        $reader->setHeaderOffset(0);
        $header = $reader->getHeader();

        if($header !== [
            'Transaction Date',
            'Transaction Type',
            'Sort Code',
            'Account Number',
            'Transaction Description',
            'Debit Amount',
            'Credit Amount',
            'Balance'
        ]) {
            return false;
        };


        


        return true;
    }

    public function import(string $data) : bool
    {
    }
}