<?php

use App\Http\Controllers\AccountCategoryController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'authenticate'])->name('login');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::apiResource('account-categories', AccountCategoryController::class)->only('index')->middleware('auth:sanctum');

Route::apiResource('accounts', AccountController::class)->middleware('auth:sanctum');

Route::apiResource('transactions', TransactionController::class)->middleware('auth:sanctum');

Route::apiResource('transaction-categories', TransactionCategoryController::class)->middleware('auth:sanctum');

