<?php

use App\Http\Controllers\Staff\PhoneController;
use App\Http\Controllers\Staff\QueueController;

//-----[訂位相關]-----//
Route::get('/queues', [QueueController::class, 'index'])->name('queues.index');
Route::post('/queues', [QueueController::class, 'store'])->name('queues.store');
Route::put('/queues/check-in', [QueueController::class, 'checkIn'])->name('queues.check-in');
Route::put('/queues/check-out/tables/{id}', [QueueController::class, 'checkout'])->name('queues.check-out');
Route::get('/queues/report', [QueueController::class, 'report'])->name('queues.report');
//-----[使用者相關]-----//
Route::get('/phones', [PhoneController::class, 'index'])->name('phones.index');
Route::put('/phones/block-phones', [PhoneController::class, 'blockPhones'])->name('phones.block-phones');
Route::put('/phones/unblock-phones', [PhoneController::class, 'unblockPhones'])->name('phones.unblock-phones');
