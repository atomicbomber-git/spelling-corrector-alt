<?php


namespace App\Support;


use PhpOffice\PhpWord\IOFactory;
use DOMDocument;

class FileConverter
{
    public static function wordToHTML(string $wordPath)
    {
        $docxReader = IOFactory::createReader("Word2007");
        $docxDocument = $docxReader->load($wordPath);

        $tempOutputFile = tempnam(storage_path("/"), "tmp");
        $htmlWriter = IOFactory::createWriter($docxDocument, "HTML");
        $htmlWriter->save($tempOutputFile);

        $outputHTML = file_get_contents($tempOutputFile);
        unlink($tempOutputFile);

        return self::extractBodyContent($outputHTML);
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