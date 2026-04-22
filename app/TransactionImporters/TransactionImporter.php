<?php

namespace App\TransactionImporters;

interface TransactionImporter
{
    public function loadData(string $data) : void;

    public function validate() : void;

    public function generateFingerprint() : string;

    public function parse() : array;
}
