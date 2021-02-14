<?php

namespace App\Http\Controllers;

use App\Constants\MessageState;
use App\Kata;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;

class WordController extends Controller
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->middleware("auth");
        $this->responseFactory = $responseFactory;
    }

    /**
     * Display a listing of the resource.
     *
     * @param ResponseFactory $responseFactory
     * @return \Illuminate\Http\Response
     */
    public function index(ResponseFactory $responseFactory)
    {
        $words = Kata::query()
            ->select("isi")
            ->paginate();

        return $responseFactory->view("word.index", compact("words"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Kata $word
     * @return \Illuminate\Http\Response
     */
    public function show(Kata $word)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Kata $word
     * @return \Illuminate\Http\Response
     */
    public function edit(Kata $word)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Kata $word
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Kata $word)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Kata $word
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Kata $word)
    {
        $word->delete();

        return redirect()->back()
            ->with("messages", [
                [
                    "isi" => __("messages.delete.success"),
                    "state" => MessageState::STATE_SUCCESS
                ]
            ]);
    }
}
