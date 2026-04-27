<?php

namespace App\Support;

use Illuminate\Http\Request;

class Pagination
{
    public static function resolvePerPage(Request $request, int $default, int $max): int
    {
        $requested = $request->integer('per_page', $default);

        return max(1, min($requested, $max));
    }
}
