<?php

namespace App\TransactionImporters;

use App\Support\Money;
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
            throw new InvalidImportFormat('Invalid file header');
        };
    }

    public function generateFingerprint() : string
    {
        $rows = array_map(
            fn (array $r) => [$r['date'], $r['description'], $r['amount']],
            $this->parse()
        );

        sort($rows);

        return hash('sha256', json_encode($rows));
    }

    public function parse() : array
    {
        $transactions = [];

        foreach ($this->csvReader->getRecords() as $record) {
            $debit = $record['Debit Amount'] === '' ? 0 : Money::toPence($record['Debit Amount']);
            $credit = $record['Credit Amount'] === '' ? 0 : Money::toPence($record['Credit Amount']);

            $transactions[] = [
                'date' => \DateTime::createFromFormat('d/m/Y', $record['Transaction Date'])->format('Y-m-d'),
                'type' => $record['Transaction Type'],
                'sort_code' => ltrim($record['Sort Code'], "'"),
                'account_number' => $record['Account Number'],
                'description' => $record['Transaction Description'],
                'amount' => $credit - $debit,
            ];
        }

        return $transactions;
    }
}
