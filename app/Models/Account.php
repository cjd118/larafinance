<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model 
{
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'account_category_id'];

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'account_category_id');
    }
}
