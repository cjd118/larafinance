<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'account_category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class, 'account_category_id');
    }

    public static function uniqueNameRule(?int $accountCategoryId, ?int $ignoreId = null): Unique
    {
        return Rule::unique('accounts', 'name')
            ->where(fn ($q) => $q->whereIn(
                'account_category_id',
                AccountCategory::where('type', fn ($qq) => $qq->select('type')
                    ->from('account_categories')
                    ->where('id', $accountCategoryId)
                )->select('id')
            ))
            ->ignore($ignoreId)
            ->whereNull('deleted_at');
    }
}
