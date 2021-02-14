<?php

namespace App\Http\Controllers;

use App\Constants\MessageState;
use App\Support\PdfImporter;
use Illuminate\Http\Request;

class ImportWordsFromDocumentController extends Controller
{
    private PdfImporter $pdfImporter;

    public function __construct(PdfImporter $pdfImporter)
    {
        $this->pdfImporter = $pdfImporter;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            "document" => ["required", "file", "mimes:pdf"]
        ]);

        $affectedCount = $this->pdfImporter->import($request->file("document"));

        return redirect()->back()
            ->with("messages", [
                [
                    "isi" => "{$affectedCount} data berhasil ditambahkan.",
                    "state" => MessageState::STATE_SUCCESS
                ]
            ]);
    }
}
