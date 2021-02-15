<?php

namespace App\Http\Controllers;

use App\FileWord;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileWordDownloadController extends Controller
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
     * @return BinaryFileResponse
     */
    public function __invoke(Request $request, FileWord $file_word)
    {
        return $this->responseFactory->download(
            $file_word->getFirstMediaPath(FileWord::COLLECTION_WORD_FILE)
        );
    }
}
