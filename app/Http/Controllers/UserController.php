<?php


namespace App\Http\Controllers;


use App\Providers\AuthServiceProvider;
use App\Support\MessageState;
use App\Support\SessionHelper;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function index()
    {
        $this->authorize(AuthServiceProvider::MANAGE_MAHASISWA);

        return $this->responseFactory->view("mahasiswa.index", [
            "mahasiswas" => User::query()
                ->where("level", User::LEVEL_MAHASISWA)
                ->orderBy("name")
                ->paginate()
        ]);
    }

    public function create()
    {
        $this->authorize(AuthServiceProvider::MANAGE_MAHASISWA);

        return $this->responseFactory->view("mahasiswa.create");
    }

    public function store(Request $request)
    {
        $this->authorize(AuthServiceProvider::MANAGE_MAHASISWA);

        $data = $request->validate([
            "name" => ["required", "string"],
            "nomor_induk" => ["required", "string", "alpha_num", Rule::unique(User::class)],
            "username" => ["required", "string", "alpha_dash", Rule::unique(User::class)],
            "password" => ["required", "string", "confirmed"],
        ]);

        User::query()->create(array_merge($data, [
            "password" => Hash::make($data["password"]),
            "level" => User::LEVEL_MAHASISWA,
        ]));

        SessionHelper::flashMessage(
            __("messages.create.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute("mahasiswa.index");
    }

    public function edit(User $mahasiswa)
    {
        $this->authorize(AuthServiceProvider::MANAGE_MAHASISWA);

        return $this->responseFactory->view("mahasiswa.edit", [
            "mahasiswa" => $mahasiswa,
        ]);
    }

    public function update(Request $request, User $mahasiswa)
    {
        $this->authorize(AuthServiceProvider::MANAGE_MAHASISWA);

        $data = $request->validate([
            "name" => ["required", "string"],
            "nomor_induk" => ["required", "string", "alpha_num", Rule::unique(User::class)->ignoreModel($mahasiswa)],
            "username" => ["required", "string", "alpha_dash", Rule::unique(User::class)->ignoreModel($mahasiswa)],
            "password" => ["nullable", "string", "confirmed"],
        ]);

        if (isset($data["password"])) {
            $data["password"] = Hash::make($data["password"]);
        } else {
            unset($data["password"]);
        }

        $mahasiswa->update($data);

        SessionHelper::flashMessage(
            __("messages.update.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute("mahasiswa.edit", $mahasiswa);
    }

    public function destroy(User $user)
    {
        DB::beginTransaction();

        $user->dokumen_words()->delete();
        $user->delete();

        DB::commit();

        SessionHelper::flashMessage(
            __("messages.delete.success"),
            MessageState::STATE_SUCCESS,
        );

        return $this->responseFactory->redirectToRoute("mahasiswa.index");
    }
}