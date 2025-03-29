<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type'];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
