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

    /**
     * @param string $html
     * @return string
     */
    public static function extractBodyContent(string $html): string
    {
        $htmlDocument = new DOMDocument();
        $htmlDocument->loadHtml($html);

        $elements = $htmlDocument->getElementsByTagName("body");
        $body = $elements->item(0);
        $tempDiv = $htmlDocument->createElement("div");
        foreach ($body->childNodes as $childNode) {
            $tempDiv->appendChild($childNode->cloneNode(true));
        }

        $htmlDocument->appendChild($tempDiv);
        return $tempDiv->C14N();
    }
}