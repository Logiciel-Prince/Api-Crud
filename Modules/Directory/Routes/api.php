<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Directory\Http\Controllers\DirectoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/directory', function (Request $request) {
    return $request->user();
});

Route::group(['as' => 'directories.'], function () {
   Route::get('directories', [DirectoryController::class, 'allDirectory'])->name('index');

    Route::post('directories', [DirectoryController::class, 'createDirectory'])->name('create');
    Route::delete('directories/{id}', [DirectoryController::class, 'deleteDirectories'])->name('delete');
    Route::post('directories/{directory}/rename', [DirectoryController::class, 'renameDirectory'])->name('rename');
});