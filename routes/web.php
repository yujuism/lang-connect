<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

// Product routes group
Route::group(['prefix' => 'products', 'controller' => ProductController::class], function () {
    // Route 5: products - GET /products -> index
    Route::get('/', 'index')->name('products');

    // Route 6: products.create - GET /products/create -> create
    Route::get('/create', 'create')->name('products.create');

    // Route 7: products.edit - GET /products/edit/{id} -> edit
    Route::get('/edit/{id}', 'edit')->name('products.edit');

    // Route 8: products.store - POST /products/store -> store
    Route::post('/store', 'store')->name('products.store');

    // Route 9: products.update - POST /products/update/{id} -> update
    Route::post('/update/{id}', 'update')->name('products.update');

    // Route 10: products.show - GET /products/show/{id} -> show
    Route::get('/show/{id}', 'show')->name('products.show');
});
