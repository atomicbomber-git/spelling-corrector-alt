<?php

namespace App\Http\Controllers;

use App\DokumenWord;
use App\Support\FileConverter;
use App\Support\MessageState;
use App\Support\SessionHelper;
use App\Support\StringUtil;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\Shared\ZipArchive;

class DokumenKoreksiEjaanController extends Controller
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param DokumenWord $dokumen_word
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, DokumenWord $dokumen_word)
    {
        $data = $request->validate([
            "correction_list" => ["array", "required"],
            "correction_list.*.original" => ["string", "required"],
            "correction_list.*.replacement" => ["string", "required"],
        ]);

        $replacementPairs = (new Collection($data["correction_list"]))
            ->pluck("replacement", "original")
            ->toArray();

        $docxFilepath = $dokumen_word->getFirstMediaPath(DokumenWord::COLLECTION_WORD_FILE);
        $docxAsZipArchive = new ZipArchive();
        $docxAsZipArchive->open($docxFilepath);

        if ($docxAsZipArchive->open($docxFilepath)) {
            $documentXmlPath = "word/document.xml";
            $documentContent = $docxAsZipArchive->getFromName($documentXmlPath);

            foreach ($replacementPairs as $original => $replacement) {
                if (strtolower($original) === strtolower($replacement)) continue;

                $original = preg_quote($original);
                $delimiter = $this->getWordDelimitersRegex();

                $documentContent = StringUtil::replaceAllRegex(
                    "/([{$delimiter}]){$original}([{$delimiter}])/i",
                    "$1{$replacement}$2",
                    $documentContent
                );
            }

            $docxAsZipArchive->addFromString($documentXmlPath, $documentContent);
            $docxAsZipArchive->close();
        } else {
            throw new \Exception("Failed to open docx file.");
        }

        $dokumen_word->update([
            "konten_html" => FileConverter::wordToHTML($docxFilepath)
        ]);

        SessionHelper::flashMessage(
            __("messages.update.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->noContent(200);
    }

    private function getWordDelimitersRegex(): string
    {
        return preg_quote(implode("", [
            ',', '<', '>', '"', '\'', '(', ')', '.', '!', '?', ' ', ':', ';', '-',
        ]));
    }
}
