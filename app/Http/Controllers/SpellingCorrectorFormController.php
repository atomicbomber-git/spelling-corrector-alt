<?php

namespace App\Http\Controllers;

use App\SimilaritasJaroWinkler;
use Illuminate\Http\Request;

class SpellingCorrectorFormController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return response()->view("spelling-corrector-form");
    }
}
