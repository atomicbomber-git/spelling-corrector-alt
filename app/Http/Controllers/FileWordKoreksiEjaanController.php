<?php

namespace App\Http\Controllers;

use App\FileWord;
use App\Support\FileConverter;
use App\Support\MessageState;
use App\Support\SessionHelper;
use App\Support\StringUtil;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\Shared\ZipArchive;

class FileWordKoreksiEjaanController extends Controller
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
     * @param FileWord $file_word
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, FileWord $file_word)
    {
        $data = $request->validate([
            "corrections" => ["array", "required"],
            "corrections.*.original" => ["required", "string"],
            "corrections.*.replacements" => ["required", "array"],
            "corrections.*.replacements.*.index" => ["required", "integer"],
            "corrections.*.replacements.*.correction" => ["required", "string"],
        ]);

        $replacementPairs = (new Collection($data["corrections"]))
            ->pluck("replacements", "original")
            ->toArray();

        $docxFilepath = $file_word->getFirstMediaPath(FileWord::COLLECTION_WORD_FILE);
        $docxAsZipArchive = new ZipArchive();
        $docxAsZipArchive->open($docxFilepath);

        if ($docxAsZipArchive->open($docxFilepath)) {
            $documentXmlPath = "word/document.xml";

            $domDocument = new DOMDocument();
            $domDocument->loadXML($docxAsZipArchive->getFromName($documentXmlPath));

            foreach ($replacementPairs as $original => $replacements) {
                $replacements = array_filter(
                    $replacements,
                    fn ($replacement) => strtolower($replacement["correction"]) !== strtolower($original)
                );

                $replacements = array_map(
                    fn ($replacement) => array_merge($replacement, [
                        "correction" => $replacement['correction']
                    ]),
                    $replacements,
                );

                $original = preg_quote($original, "/");

                $domDocument = StringUtil::replaceAllRegexMultipleInXmlNode(
                    "/(\b)({$original})(\b)/i",
                    $replacements,
                    $domDocument,
                );
            }

            $docxAsZipArchive->addFromString($documentXmlPath, $domDocument->C14N());
            $docxAsZipArchive->close();
        } else {
            throw new \Exception("Failed to open docx file.");
        }

        $file_word->update([
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
            ',', '<', '>', '"', '\'', '(', ')', '.', '!', '?', ' ', ':', ';',
        ]));
    }
}
