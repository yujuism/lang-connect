<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HomeController;

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

// Home route
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/quick-search', [HomeController::class, 'quickSearch'])->name('home.quick-search');

// Product routes group
Route::group(['prefix' => 'products', 'controller' => ProductController::class], function () {
    // Main product routes
    Route::get('/', 'index')->name('products');
    Route::get('/create', 'create')->name('products.create');
    Route::get('/edit/{id}', 'edit')->name('products.edit');
    Route::post('/store', 'store')->name('products.store');
    Route::post('/update/{id}', 'update')->name('products.update');
    Route::get('/show/{id}', 'show')->name('products.show');
    Route::delete('/delete/{id}', 'destroy')->name('products.destroy');

    // API routes for AJAX
    Route::get('/search-suggestions', 'searchSuggestions')->name('products.search-suggestions');
});

// Category routes (optional for future use)
Route::get('/categories/{category:slug}', function($category) {
    return redirect()->route('products', ['category' => $category->id]);
})->name('categories.show');
