<?php

namespace App\TransactionImporters;

use League\Csv\Reader;

class LloydsBankCsvTransactionImporter implements TransactionImporter
{
    private $csvReader;

    public function loadData(string $data) : void
    {       
        $this->csvReader = Reader::createFromString($data);
        $this->csvReader->setHeaderOffset(0);
    }

    public function validate() : void {
        $this->validateHeader($this->csvReader->getHeader());
    }

    private function validateHeader(array $header) : void
    {   
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
            throw new \Exception('Invalid file header');
        };
    }
    
    public function generateFingerprint() : string
    {
        return hash('sha256', $this->csvReader->__toString());
    }

    public function import() : bool
    {
        //todo
        return false;
    }
}
