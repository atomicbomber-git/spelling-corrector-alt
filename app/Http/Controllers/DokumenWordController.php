<?php

namespace App\Http\Controllers;

use App\Constants\MessageState;
use App\DokumenWord;
use App\Support\FileConverter;
use App\Support\SessionHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use NlpTools\Tokenizers\TokenizerInterface;
use NlpTools\Tokenizers\WhitespaceTokenizer;

class DokumenWordController extends Controller
{
    private ResponseFactory $responseFactory;
    private TokenizerInterface $tokenizer;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->middleware("auth");
        $this->responseFactory = $responseFactory;
        $this->tokenizer = new WhitespaceTokenizer();
    }

    public function index(): Response
    {
        return $this->responseFactory->view("dokumen-word.index", [
            "dokumen_words" => DokumenWord::query()
                ->orderByDesc("updated_at")
                ->orderByDesc("created_at")
                ->paginate()
        ]);
    }

    public function show(Request $request, DokumenWord $dokumen_word)
    {
        if ($request->ajax()) {
            return $this->responseFactory->json($dokumen_word);
        }

        return $this->responseFactory->view("dokumen-word.show", [
            "dokumen_word" => $dokumen_word->makeHidden("konten_html"),
        ]);
    }

    public function create()
    {
        return $this->responseFactory->view("dokumen-word.create");
    }

    public function edit(DokumenWord $dokumen_word)
    {
        return $this->responseFactory->view("dokumen-word.edit", [
            "dokumen_word" => $dokumen_word,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "nama" => ["required", "string", "unique:dokumen_word"],
            "berkas" => ["required", "file", "mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
        ], [
            "berkas.mimetypes" => "Berkas harus dalam format .docx",
        ]);

        DB::beginTransaction();

        $dokumenWord = DokumenWord::query()
            ->create([
                "user_id" => Auth::id(),
                "nama" => $data["nama"],
                "konten_html" => FileConverter::wordToHTML(
                    $request->file("berkas")->getRealPath(),
                )
            ]);

        $dokumenWord
            ->addMediaFromRequest("berkas")
            ->toMediaCollection(DokumenWord::COLLECTION_WORD_FILE);

        DB::commit();

        SessionHelper::flashMessage(
            __("messages.create.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute(
            "dokumen-word.show",
            $dokumenWord
        );
    }

    public function update(Request $request, DokumenWord $dokumen_word)
    {
        $data = $request->validate([
            "nama" => ["required", "string", Rule::unique(DokumenWord::class)->ignoreModel($dokumen_word)],
            "berkas" => ["nullable", "file", "mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
        ], [
            "berkas.mimetypes" => "Berkas harus dalam format .docx",
        ]);

        DB::beginTransaction();

        $dokumen_word->fill([
            "nama" => $data["nama"]
        ]);

        if ($request->hasFile("berkas")) {
            $dokumen_word->fill([
                "konten_html" => FileConverter::wordToHTML(
                    $request->file("berkas")->getRealPath(),
                )
            ]);

            $dokumen_word
                ->addMediaFromRequest("berkas")
                ->toMediaCollection(DokumenWord::COLLECTION_WORD_FILE);
        }

        $dokumen_word->save();

        DB::commit();

        SessionHelper::flashMessage(
            __("messages.update.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute("dokumen-word.show", $dokumen_word);
    }

    public function destroy(DokumenWord $dokumen_word)
    {
        DB::beginTransaction();

        $dokumen_word->media()->delete();
        $dokumen_word->delete();

        DB::commit();

        SessionHelper::flashMessage(
            __("messages.update.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute("dokumen-word.index");
    }
}
