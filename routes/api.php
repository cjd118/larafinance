<?php

use App\Http\Controllers\AccountCategoryController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;


Route::apiResource('account-categories', AccountCategoryController::class)->only('index')->middleware(env('APP_ENV') === 'local' ? [] : 'auth:sanctum');

Route::apiResource('accounts', AccountController::class)->only('index')->middleware(env('APP_ENV') === 'local' ? [] : 'auth:sanctum');


