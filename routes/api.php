<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 语言切换 API 路由
Route::get('/locale/current', [App\Http\Controllers\LocaleController::class, 'current'])->name('locale.current');
Route::post('/locale/switch', [App\Http\Controllers\LocaleController::class, 'apiSwitch'])->name('locale.api.switch');
