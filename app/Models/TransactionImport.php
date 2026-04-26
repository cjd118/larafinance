<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionImport extends Model
{
    use SoftDeletes;

    protected $fillable = [];

    public function transactionImporter()
    {
        return $this->belongsTo(TransactionImporter::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
