<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductEntryController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [ProductEntryController::class, 'index']);
Route::get('/entries', [ProductEntryController::class, 'list']);     
Route::post('/entries', [ProductEntryController::class, 'store']);   
Route::put('/entries/{id}', [ProductEntryController::class, 'update']);
Route::delete('/entries/{id}', [ProductEntryController::class, 'destroy']);