<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BasketController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::get('product/{id}', [ProductController::class, 'index']);
Route::group(['prefix' => 'product' , 'middleware' => ['auth:sanctum']], function (){
    Route::post('store', [ProductController::class, 'store']);
    Route::post('{id}/update', [ProductController::class, 'update']);
    Route::post('{id}/delete', [ProductController::class, 'destroy']);
});

Route::group(['prefix' => 'basket' , 'middleware' => ['auth:sanctum']], function (){
    Route::post('/new', [BasketController::class, 'newBasket']);
    Route::post('/items', [BasketController::class, 'items']);
    Route::post('/item/add', [BasketController::class, 'itemAdd']);
    Route::post('/item/delete', [BasketController::class, 'itemDelete']);

});

