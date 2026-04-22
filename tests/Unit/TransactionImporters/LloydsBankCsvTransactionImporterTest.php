<?php

namespace Tests\Unit\TransactionImporters;

use App\TransactionImporters\LloydsBankCsvTransactionImporter;
use PHPUnit\Framework\TestCase;

class LloydsBankCsvTransactionImporterTest extends TestCase
{
    public function testParseReturnsNormalisedTransactions(): void
    {
        $csv = "Transaction Date,Transaction Type,Sort Code,Account Number,Transaction Description,Debit Amount,Credit Amount,Balance\n";
        $csv .= "30/01/2026,DEB,'01-02-03,12345678,mobile,8.00,,680.65\n";
        $csv .= "26/01/2026,TFR,'01-02-03,12345678,J SMITH    24JAN26,,800.00,1318.18\n";

        $importer = new LloydsBankCsvTransactionImporter();
        $importer->loadData($csv);

        $this->assertEquals([
            [
                'date' => '2026-01-30',
                'type' => 'DEB',
                'sort_code' => '01-02-03',
                'account_number' => '12345678',
                'description' => 'mobile',
                'amount' => -8.00,
            ],
            [
                'date' => '2026-01-26',
                'type' => 'TFR',
                'sort_code' => '01-02-03',
                'account_number' => '12345678',
                'description' => 'J SMITH    24JAN26',
                'amount' => 800.00,
            ],
        ], $importer->parse());
    }
}
