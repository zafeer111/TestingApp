<?php

use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ShopifyProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return view('welcome');
})->middleware(['verify.shopify'])->name('home');


Route::get('/import-products', [ShopifyProductController::class, 'importProducts']);