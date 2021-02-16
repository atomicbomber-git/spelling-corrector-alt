<?php

namespace App\Http\Controllers;

use App\Constants\MessageState;
use App\FileWord;
use App\Support\FileConverter;
use App\Support\SessionHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use NlpTools\Tokenizers\TokenizerInterface;
use NlpTools\Tokenizers\WhitespaceTokenizer;

class FileWordController extends Controller
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
        return $this->responseFactory->view("file-word.index", [
            "file_words" => FileWord::query()
                ->where("user_id", Auth::id())
                ->orderByDesc("updated_at")
                ->orderByDesc("created_at")
                ->paginate()
        ]);
    }

    public function show(Request $request, FileWord $file_word)
    {
        if ($request->ajax()) {
            return $this->responseFactory->json($file_word);
        }

        return $this->responseFactory->view("file-word.show", [
            "file_word" => $file_word->makeHidden("konten_html"),
        ]);
    }

    public function create()
    {
        return $this->responseFactory->view("file-word.create");
    }

    public function edit(FileWord $file_word)
    {
        return $this->responseFactory->view("file-word.edit", [
            "file_word" => $file_word,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "nama" => ["required", "string", Rule::unique(FileWord::class)],
            "berkas" => ["required", "file", "mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
        ], [
            "berkas.mimetypes" => "Berkas harus dalam format .docx",
        ]);

        DB::beginTransaction();

        $dokumenWord = FileWord::query()
            ->create([
                "user_id" => Auth::id(),
                "nama" => $data["nama"],
                "konten_html" => FileConverter::wordToHTML(
                    $request->file("berkas")->getRealPath(),
                )
            ]);

        $dokumenWord
            ->addMediaFromRequest("berkas")
            ->toMediaCollection(FileWord::COLLECTION_WORD_FILE);

        DB::commit();

        SessionHelper::flashMessage(
            __("messages.create.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute(
            "file-word.show",
            $dokumenWord
        );
    }

    public function update(Request $request, FileWord $file_word)
    {
        $data = $request->validate([
            "nama" => ["required", "string", Rule::unique(FileWord::class)->ignoreModel($file_word)],
            "berkas" => ["nullable", "file", "mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
        ], [
            "berkas.mimetypes" => "Berkas harus dalam format .docx",
        ]);

        DB::beginTransaction();

        $file_word->fill([
            "nama" => $data["nama"]
        ]);

        if ($request->hasFile("berkas")) {
            $file_word->fill([
                "konten_html" => FileConverter::wordToHTML(
                    $request->file("berkas")->getRealPath(),
                )
            ]);

            $file_word
                ->addMediaFromRequest("berkas")
                ->toMediaCollection(FileWord::COLLECTION_WORD_FILE);
        }

        $file_word->save();

        DB::commit();

        SessionHelper::flashMessage(
            __("messages.update.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute("file-word.show", $file_word);
    }

    public function destroy(FileWord $file_word)
    {
        DB::beginTransaction();

        $file_word->media()->delete();
        $file_word->delete();

        DB::commit();

        SessionHelper::flashMessage(
            __("messages.update.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute("file-word.index");
    }
}
