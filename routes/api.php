<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['jwt.admin'])->group(function() {

    Route::post('user/invite',[App\Http\Controllers\UsersController::class, 'invite']);
});
Route::middleware(['jwt.user'])->group(function() {

    Route::post('updateprofile',[App\Http\Controllers\UsersController::class, 'updateprofile'])->name('updateprofile');

});


Route::post('signUp/{token}',[App\Http\Controllers\UsersController::class, 'signUp'])->name('signUp');
Route::post('user/login',[App\Http\Controllers\UsersController::class, 'login']);
Route::post('emailverify',[App\Http\Controllers\UsersController::class, 'emailverify'])->name('emailverify');








