<?php

use App\Http\Controllers\AccountCategoryController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountRoutingRuleController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionImportController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'authenticate'])->middleware('throttle:5,1')->name('login');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::apiResource('account-categories', AccountCategoryController::class)->only('index')->middleware('auth:sanctum');

Route::apiResource('accounts', AccountController::class)->middleware('auth:sanctum');

Route::apiResource('transactions', TransactionController::class)->middleware('auth:sanctum');

Route::apiResource('transaction-categories', TransactionCategoryController::class)->middleware('auth:sanctum');

Route::apiResource('transaction-imports', TransactionImportController::class)->only('store')->middleware('auth:sanctum');

Route::apiResource('account-routing-rules', AccountRoutingRuleController::class)->middleware('auth:sanctum');

