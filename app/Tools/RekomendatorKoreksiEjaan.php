<?php


namespace App\Tools;


use App\FrekuensiNgram;
use App\Kata;
use Illuminate\Support\Collection;
use NlpTools\Tokenizers\RegexTokenizer;
use NlpTools\Tokenizers\TokenizerInterface;

class RekomendatorKoreksiEjaan
{
    public const MAX_RECOMMENDATIONS = 10;
    public const BOBOT_SIMILARITAS_DB = 0.8;
    public const BOBOT_FREKUENSI_NGRAM = 0.2;

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
        $this->preprocess();
    }

    private function preprocess()
    {
        $this->tokens = $this->filterTokens($this->tokens);
    }

    private function filterTokens(array $tokens): array
    {
        $tokens = array_values(array_filter(
            $tokens,
            fn($token) => strlen($token) > 0
        ));

        return $tokens;
    }

    public function recommendations(): array
    {
        $outputTokens = [];

        foreach (range(0, count($this->tokens) - 1) as $index) {
            $token = $this->tokens[$index];

            $prev_word_1 = $this->tokens[$index - 2] ?? null;
            $prev_word_2 = $this->tokens[$index - 1] ?? null;
            $isIncorrect = $this->tokenDoesntExistOnDictionary($token);

            if (!$isIncorrect) continue;

            $outputTokens[] = [
                "token" => $token,
                "recommendations" => $this->getRecommendations($token, $prev_word_1, $prev_word_2),
            ];
        }

        return $outputTokens;
    }

    public function tokenDoesntExistOnDictionary(string $token): bool
    {
        return !$this->tokenExistsInDictionary($token);
    }

    public function tokenExistsInDictionary(string $token): bool
    {
        return Kata::query()
                ->whereRaw("LOWER(isi) = LOWER(?)", [$token])
                ->count() > 0;
    }

    /**
     * @param string $cleanedToken
     * @param string|null $prev_word_1
     * @param string|null $prev_word_2
     * @return array
     */
    public function getRecommendations(string $cleanedToken, ?string $prev_word_1, ?string $prev_word_2): array
    {
        $most_similar_words = $this->getMostSimilarWords($cleanedToken, self::MAX_RECOMMENDATIONS)->pluck("points", "word");
        $most_frequent_ngram_frequencies = $this->getMostFrequentNgramFrequencies($prev_word_1, $prev_word_2)->pluck("points", "word");

        return (new Collection())
            ->merge($most_similar_words->keys())
            ->merge($most_frequent_ngram_frequencies->keys())
            ->map(function ($word) use ($most_similar_words, $most_frequent_ngram_frequencies) {
                return [
                    "word" => $word,
                    "points" =>
                        ($most_similar_words[$word] ?? 0 * self::BOBOT_SIMILARITAS_DB) +
                        ($most_frequent_ngram_frequencies[$word] ?? 0 * self::BOBOT_FREKUENSI_NGRAM)
                ];
            })
            ->sortByDesc("points")
            ->pluck("word")
            ->toArray();
    }

    private function getMostSimilarWords(string $incorrectWord, int $max = 5): Collection
    {
        return Kata::query()
            ->select("isi AS word")
            ->selectRaw("(isi <-> ?) AS points", [$incorrectWord])
            ->orderByRaw("isi <-> ?", [$incorrectWord])
            ->limit($max)
            ->get();
    }

    public function getMostFrequentNgramFrequencies(?string $word_1, ?string $word_2, $candidates = []): Collection
    {
        $most_frequent_ngram_frequencies = new Collection();

        $most_frequent_ngram_frequencies = $most_frequent_ngram_frequencies->merge(
            FrekuensiNgram::query()
                ->select("gram_3", "frekuensi")
                ->where(["gram_1" => $word_1, "gram_2" => $word_2])
                ->orderByDesc("frekuensi")
                ->limit(self::MAX_RECOMMENDATIONS)
                ->get()
        );

        $most_frequent_ngram_frequencies->merge(
            FrekuensiNgram::query()
                ->select("gram_3", "frekuensi")
                ->where(["gram_1" => $word_1, "gram_2" => $word_2])
                ->whereIn("gram_3", $candidates)
                ->orderByDesc("frekuensi")
                ->get()
        );

        $ngram_frekuensi_sum = $most_frequent_ngram_frequencies->sum("frekuensi");

        return $most_frequent_ngram_frequencies->map(fn($ngram_frekuensi) => [
            "word" => $ngram_frekuensi->gram_3,
            "points" => $ngram_frekuensi->frekuensi / $ngram_frekuensi_sum,
        ]);
    }
}