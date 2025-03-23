<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountCategoryCollection;
use App\Models\AccountCategory;
use Illuminate\Http\Request;

class AccountCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new AccountCategoryCollection(AccountCategory::orderBy('id', 'desc')->get());
    }
}
