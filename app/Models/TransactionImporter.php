<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionImporter extends Model
{
    protected $fillable = ['name', 'class_name'];
}
