<?php


namespace App\Support;


use Illuminate\Support\Facades\Http;
use DOMDocument;

class FileConverter
{
    public static function wordToHTML(string $wordPath): string
    {
        $response = Http::attach(
            "file",
            file_get_contents($wordPath),
            "document.docx"
        )->post(config("application.document_server_url") . "/word-to-html");
        return $response->body();
    }

    public static function HTMLToWord(string $htmlString): string
    {
        $response = Http::attach(
            "file",
            $htmlString,
            "input.docx"
        )->post(config("application.document_server_url") . "/html-to-word");

        return $response->body();
    }
}