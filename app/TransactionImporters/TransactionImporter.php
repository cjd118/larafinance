<?php

namespace App\TransactionImporters;

interface TransactionImporter
{
    public function loadData(string $data) : void;

    public function validate() : void;

    public function generateFingerprint() : string;

    /**
     * Returns an array of normalised transaction rows. Each row's `amount` is
     * a signed integer in pence: positive for credits, negative for debits.
     */
    public function parse() : array;
}
