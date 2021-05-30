<?php


namespace App\Tools;


use App\FrekuensiNgram;
use App\Kata;
use DB;
use Illuminate\Support\Collection;
use NlpTools\Tokenizers\RegexTokenizer;
use NlpTools\Tokenizers\TokenizerInterface;

class RekomendatorKoreksiEjaan
{
    public const MAX_RECOMMENDATIONS = 50;

    public string $text;
    public array $tokens;

    public TokenizerInterface $tokenizer;

    public function __construct(array $tokens)
    {
        $this->tokenizer = new RegexTokenizer(
            array_map(fn($pattern) => "/" . preg_quote($pattern) . "/", [
                ',', '<', '>', '"', '\'', '(', ')', '.', '!', '?', ' ', ':', ';', '-'
            ])
        );

        $this->tokens = $tokens;
    }

    public function recommendations(): array
    {
        $outputTokens = [];

        $tokensThatExistsInDictionary = Kata::query()
            ->selectRaw("LOWER(isi) AS isi")
            ->whereIn(
                DB::raw("LOWER(isi)"),
                collect($this->tokens)
                    ->map(fn($token) => mb_strtolower($token))
                    ->unique()
                    ->toArray()
            )
            ->pluck("isi");

        foreach (range(0, count($this->tokens) - 1) as $index) {
            $token = $this->tokens[$index];
            $prev_word = $this->tokens[$index - 1] ?? null;
            $post_word = $this->tokens[$index + 1] ?? null;

            if (in_array(mb_strtolower($token), $tokensThatExistsInDictionary->toArray())) continue;

            $outputTokens[] = [
                "token" => $token,
                "recommendations" => $this->getRecommendations($token, $prev_word, $post_word),
            ];
        }

        return $outputTokens;
    }

    public function getRecommendations(
        string $word,
        ?string $pre_word,
        ?string $post_word,
    ): array
    {
        $word = mb_strtolower($word);
        $pre_word = mb_strtolower($pre_word);
        $post_word = mb_strtolower($post_word);

        $most_similar_words = $this->getMostSimilarWords(
            $word,
            self::MAX_RECOMMENDATIONS
        )->pluck("points", "word");

        $pre_ngram_frequencies = FrekuensiNgram::query()
                ->where(["gram_1" => $pre_word])
                ->whereIn("gram_2", $most_similar_words->keys())
                ->pluck("frekuensi", "gram_2");

        $pre_ngram_frequencies = $pre_ngram_frequencies->mapWithKeys(function ($frequency, $word) use ($pre_ngram_frequencies) {
            return [$word => $frequency / $pre_ngram_frequencies->sum()];
        });

        $post_ngram_frequencies = FrekuensiNgram::query()
            ->whereIn("gram_1", $most_similar_words->keys())
            ->where(["gram_2" => $post_word,])
            ->pluck("frekuensi", "gram_1");

        $post_ngram_frequencies = $post_ngram_frequencies->mapWithKeys(function ($frequency, $word) use ($post_ngram_frequencies) {
            return [$word => $frequency / $post_ngram_frequencies->sum()];
        });

        $results = $most_similar_words->keys()
            ->map(function (string $word) use ($most_similar_words, $pre_ngram_frequencies, $post_ngram_frequencies) {
                return [
                    "word" => $word,
                    "similaritas_trigram_huruf" => $most_similar_words->get($word, 0.0),
                    "frekuensi_bigram_awal" => $pre_ngram_frequencies->get($word, 0.0),
                    "frekuensi_bigram_akhir" => $post_ngram_frequencies->get($word, 0.0),
                    "points" =>
                        collect([
                            $most_similar_words->get($word, 0.0),
                            $pre_ngram_frequencies->get($word, 0.0),
                            $post_ngram_frequencies->get($word, 0.0)
                        ])->avg()
                ];
            })
            ->sortByDesc("points")
            ->values()
            ->take(5);

        return $results->toArray();
    }

    private function getMostSimilarWords(string $incorrectWord, int $max = 5): Collection
    {
        return Kata::query()
            ->select("isi AS word")
            ->selectRaw("similarity(isi, ?) AS points", [$incorrectWord])
            ->orderByRaw("similarity(isi, ?) DESC", [$incorrectWord])
            ->limit($max)
            ->get();
    }
}