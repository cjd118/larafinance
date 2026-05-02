<?php

namespace App\TransactionImporters;

use App\Support\Money;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use League\Csv\Reader;

class LloydsBankCsvTransactionImporter implements TransactionImporter
{
    private $csvReader;

    public function loadData(string $data) : void
    {
        $this->csvReader = Reader::fromString($data);
        $this->csvReader->setHeaderOffset(0);
    }

    public function validate() : void {
        $header = $this->csvReader->getHeader();

        // Real Lloyds exports include a trailing comma on the header line,
        // which League CSV reads as an extra empty-string column.
        while (end($header) === '') {
            array_pop($header);
        }

        $this->validateHeader($header);
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
            try {
                $debit = $record['Debit Amount'] === '' ? 0 : Money::toPence($record['Debit Amount']);
                $credit = $record['Credit Amount'] === '' ? 0 : Money::toPence($record['Credit Amount']);
            } catch (\InvalidArgumentException $e) {
                throw new InvalidImportFormat("Invalid amount: {$e->getMessage()}", previous: $e);
            }

            try {
                $date = Carbon::createFromFormat('!d/m/Y', $record['Transaction Date']);
            } catch (InvalidFormatException $e) {
                throw new InvalidImportFormat("Invalid date: {$record['Transaction Date']}", previous: $e);
            }

            $transactions[] = [
                'date' => $date->format('Y-m-d'),
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
