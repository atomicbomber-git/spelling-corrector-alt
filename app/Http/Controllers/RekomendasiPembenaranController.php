<?php

namespace App\Http\Controllers;

use App\Tools\RekomendatorKoreksiEjaan;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

class RekomendasiPembenaranController extends Controller
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            "tokens" => ["required", "array"],
            "tokens.*" => ["required", "string"]
        ]);

        $recommender = new RekomendatorKoreksiEjaan(
            $data["tokens"]
        );

        return $this->responseFactory->json($recommender->recommendations());
    }
}
