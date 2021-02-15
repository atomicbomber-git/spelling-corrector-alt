<?php

use App\Http\Controllers\FileWordController;
use App\Http\Controllers\FileWordDownloadController;
use App\Http\Controllers\ImportWordsFromDocumentController;
use App\Http\Controllers\FileWordKoreksiEjaanController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\RekomendasiPembenaranController;
use App\Http\Controllers\WordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    "login"
]);

Route::redirect("/", "/login");
Route::resource("mahasiswa", MahasiswaController::class);
Route::resource("file-word", FileWordController::class);
Route::get("file-word/{file_word}/download", FileWordDownloadController::class)->name("file-word.download");
Route::post("file-word/{file_word}/koreksi", FileWordKoreksiEjaanController::class)->name("file-word.koreksi-ejaan");
Route::post("rekomendasi-pembenaran", RekomendasiPembenaranController::class)->name("rekomendasi-pembenaran");
