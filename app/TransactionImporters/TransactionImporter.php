<?php

namespace App\TransactionImporters;

interface TransactionImporter
{
    public function validate(string $data) : bool;

    public function import(string $data) : bool;
}