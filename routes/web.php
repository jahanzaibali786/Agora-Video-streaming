<?php

use App\Http\Controllers\StreamingController;
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
});
// Route::middleware('auth')->group(function () {  \\ can apply if authentication is integrated in project
    Route::get('/stream/noposter', function () {
        // dd(auth()->id());
        return response()->file(public_path('frontend/girl/img16.png'));
    });
    Route::get('/stream/index', [StreamingController::class, 'index'])->name('stream.index');
    Route::get('/streams', [StreamingController::class, 'listStreams'])->name('stream.list');
    Route::get('/stream/watch-token/{streamKey}', [StreamingController::class, 'getWatchToken']);
    Route::get('/stream/{id}', [StreamingController::class, 'watchStream'])->name('stream.watch');
    Route::post('/stream/start', [StreamingController::class, 'startStream'])->name('stream.start');
    Route::post('/stream/end', [StreamingController::class, 'endStream'])->name('stream.end');
    Route::post('/stream/stop', [StreamingController::class, 'stop'])->name('stream.stop');

    //calling 
    Route::get('/privatecall',[Callcontroller::class,'index'])->name('privatecall');
// });