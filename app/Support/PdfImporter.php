<?php


namespace App\Support;


use App\SimilaritasJaroWinkler;
use Illuminate\Support\Facades\DB;
use Sastrawi\Tokenizer\TokenizerInterface;
use Spatie\PdfToText\Pdf;

class PdfImporter
{
    private TokenizerInterface $tokenizer;

    public function __construct(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function import(string $filePath): int
    {
        $text = Pdf::getText($filePath);
        $tokens = $this->tokenizer->tokenize($text);
        $tokens = array_filter($tokens, fn($token) => ctype_alpha($token));
        $tokens = array_map(fn($token) => strtolower($token), $tokens);
        $datetime = now();

        return DB::table((new SimilaritasJaroWinkler())->getTable())
            ->insertOrIgnore(
                array_map(fn($token) => [
                    "isi" => $token,
                    "created_at" => $datetime,
                    "updated_at" => $datetime,
                ], $tokens)
            );
    }
}
