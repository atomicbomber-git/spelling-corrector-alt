<?php

use App\Http\Controllers\DokumenWordController;
use App\Http\Controllers\DokumenWordDownloadController;
use App\Http\Controllers\ImportWordsFromDocumentController;
use App\Http\Controllers\DokumenKoreksiEjaanController;
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
Route::resource("dokumen-word", class_basename(DokumenWordController::class));
Route::get("dokumen-word/{dokumen_word}/download", class_basename(DokumenWordDownloadController::class))->name("dokumen-word.download");
Route::post("dokumen-word/{dokumen_word}/koreksi", class_basename(DokumenKoreksiEjaanController::class))->name("dokumen-word.koreksi-ejaan");
Route::post("rekomendasi-pembenaran", class_basename(RekomendasiPembenaranController::class))->name("rekomendasi-pembenaran");
Route::resource("/word", class_basename(WordController::class));
Route::post("/import-words-from-document", class_basename(ImportWordsFromDocumentController::class))->name("import-words-from-document");
