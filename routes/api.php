<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

Route::group(['middleware' => ['auth:api','check.level:ADMIN']], function () {

    // Customer
    Route::post('customer', [CustomerController::class, 'index']);
    Route::post('customer/save', [CustomerController::class, 'save']);
    Route::get('customer/all', [CustomerController::class, 'allCustomer']);
    Route::post('customer/update', [CustomerController::class, 'update']);
    Route::post('customer/delete', [CustomerController::class, 'delete']);
    Route::get('customer/view/{id}', [CustomerController::class, 'view']);

    // Item
    Route::post('item', [ItemController::class, 'index']);
    Route::post('item/save', [ItemController::class, 'save']); 
    Route::post('item/update', [ItemController::class, 'update']);
    Route::post('item/delete', [ItemController::class, 'delete']);
    Route::get('item/view/{id}', [ItemController::class, 'view']);
});

Route::group(['middleware' => ['auth:api']], function () {

     // Invoice
     Route::post('invoice', [InvoiceController::class, 'index']);
     Route::post('invoice/save', [InvoiceController::class, 'save']); 
     Route::post('invoice/update', [InvoiceController::class, 'update']);
     Route::post('invoice/delete', [InvoiceController::class, 'delete']);
     Route::get('invoice/view/{id}', [InvoiceController::class, 'view']);
     
});