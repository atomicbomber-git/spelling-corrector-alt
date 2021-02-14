<?php

namespace App\Http\Controllers;

use App\DokumenWord;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DokumenWordDownloadController extends Controller
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
     * @param DokumenWord $dokumen_word
     * @return BinaryFileResponse
     */
    public function __invoke(Request $request, DokumenWord $dokumen_word)
    {
        return $this->responseFactory->download(
            $dokumen_word->getFirstMediaPath(DokumenWord::COLLECTION_WORD_FILE)
        );
    }
}
