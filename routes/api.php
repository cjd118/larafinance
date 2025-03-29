<?php

use App\Http\Controllers\AccountCategoryController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;


Route::apiResource('account-categories', AccountCategoryController::class)->only('index')->middleware(env('APP_ENV') === 'local' ? [] : 'auth:sanctum');

Route::apiResource('accounts', AccountController::class)->middleware(env('APP_ENV') === 'local' ? [] : 'auth:sanctum');

Route::apiResource('transactions', TransactionController::class)->middleware(env('APP_ENV') === 'local' ? [] : 'auth:sanctum');

Route::apiResource('transaction-categories', TransactionCategoryController::class)->middleware(env('APP_ENV') === 'local' ? [] : 'auth:sanctum');

