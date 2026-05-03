<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Account $account
 */
class AccountRoutingRule extends Model
{
    protected $fillable = ['match_text', 'mode', 'account_id', 'sort_order', 'enabled'];

    protected $casts = [
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public static function findMatch(bool $isCredit, string $description): ?self
    {
        $expectedType = $isCredit ? 'credit' : 'debit';

        return self::where('enabled', true)
            ->with('account.category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->first(function (self $rule) use ($expectedType, $description) {
                if ($rule->account->category->type !== $expectedType) {
                    return false;
                }

                return match ($rule->mode) {
                    'contains' => stripos($description, $rule->match_text) !== false,
                    'exact' => strcasecmp($description, $rule->match_text) === 0,
                };
            });
    }
}
