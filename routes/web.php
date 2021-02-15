<?php

use App\FileWord;
use App\Http\Controllers\FileWordController;
use App\Http\Controllers\FileWordDownloadController;
use App\Http\Controllers\FileWordKoreksiEjaanController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\RekomendasiPembenaranController;
use App\Support\DomNodeTraverser;
use App\Support\FileConverter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpWord\Shared\ZipArchive;

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


Route::get("html/{file_word}", function (FileWord $file_word) {
    return FileConverter::wordToHTML($file_word->getFirstMediaPath(FileWord::COLLECTION_WORD_FILE));
});

Route::get("xxx/{file_word}", function (FileWord $file_word) {
    $docxFilepath = $file_word->getFirstMediaPath(FileWord::COLLECTION_WORD_FILE);
    $docxAsZipArchive = new ZipArchive();
    $docxAsZipArchive->open($docxFilepath);

    if ($docxAsZipArchive->open($docxFilepath)) {
        $documentXmlPath = "word/document.xml";
        $documentContent = $docxAsZipArchive->getFromName($documentXmlPath);
        $domObject = new DOMDocument();
        $domObject->loadXML($documentContent);

        $matches = [];

        DomNodeTraverser::traverse($domObject, function (DOMNode $node) use (&$matches) {
//            if ($node->nodeType === XML_TEXT_NODE) {
//                preg_match("/\b(apabila)\b/i", $node->wholeText, $matches);
//                if ($matches !== []) {
//                    ddd($matches);
//                }
//            }
        });

        return $domObject->C14N();

        $docxAsZipArchive->close();
    }
});

Route::redirect("/", "/login");
Route::resource("mahasiswa", MahasiswaController::class);
Route::resource("file-word", FileWordController::class);
Route::get("file-word/{file_word}/download", FileWordDownloadController::class)->name("file-word.download");
Route::post("file-word/{file_word}/koreksi", FileWordKoreksiEjaanController::class)->name("file-word.koreksi-ejaan");
Route::post("rekomendasi-pembenaran", RekomendasiPembenaranController::class)->name("rekomendasi-pembenaran");
