<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'type', 'account_category_id'];

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'account_category_id');
    }
}
