<?php

namespace App\Http\Controllers;

use App\FileWord;
use App\Support\FileConverter;
use App\Support\MessageState;
use App\Support\SessionHelper;
use App\Support\StringUtil;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     * @param Request $request
     * @param FileWord $file_word
     * @return Response
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

        $domDocument = new DOMDocument();

        $domDocument->loadHTML(
            mb_convert_encoding($file_word->loadHTML(), 'HTML-ENTITIES', 'UTF-8')
        );

        foreach ($replacementPairs as $original => $replacements) {
            $original = preg_quote($original, "/");

            $domDocument = StringUtil::replaceTextsInXmlTreeNodes(
                "/(?<!\pL)({$original})(?!\pL)/ui",
                $replacements,
                $domDocument,
            );
        }

        DB::beginTransaction();

        $file_word->saveHtml(
            $domDocument->saveHTML()
        );

        /*
         * Send wrapped HTML content to server, get docx data
         * Replace current docx data with data obtained from server
         * */
        $wrappedHTML = $this->getWrappedHTMLFromFileWord($file_word);

        $docxFileContentInStringForm = FileConverter::HTMLToWord($wrappedHTML);

        $file_word
            ->addMediaFromString($docxFileContentInStringForm)
            ->usingFileName(
                pathinfo($file_word->getFirstMediaPath(FileWord::COLLECTION_WORD_FILE))["basename"]
            )
            ->toMediaCollection(FileWord::COLLECTION_WORD_FILE);
        DB::commit();

        SessionHelper::flashMessage(
            __("messages.update.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->noContent(200);
    }

    /**
     * @param FileWord $file_word
     * @return string
     */
    public function getWrappedHTMLFromFileWord(FileWord $file_word): string
    {
        $htmlContent = $file_word->loadHTML();

        return <<<HERE
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    >
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge"
    >
    <title> $file_word->nama </title>
</head>
<body>
    $htmlContent
</body>
</html>
HERE;
    }
}
