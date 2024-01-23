<?php

use App\Http\Controllers\Frontend\QueueController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/queues', [QueueController::class, 'index'])->name('queues.index');
Route::get('/queues/{code}', [QueueController::class, 'show'])->name('queues.show');
Route::post('/queues', [QueueController::class, 'store'])->name('queues.store');
