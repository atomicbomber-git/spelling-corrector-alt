<?php


namespace App\Tools;


use App\FrekuensiNgram;
use App\Kata;
use DB;
use Illuminate\Support\Collection;
use SplFileObject;

class RekomendatorKoreksiEjaan
{
    public const MAX_RECOMMENDATIONS = 30;

    public string $text;
    public array $tokens;

    public function __construct(array $tokens)
    {
        $this->tokens = $this->filterTokens($tokens);
    }

    public function filterTokens(array $tokens): array
    {
        return array_values(
            array_filter(
                $tokens,
                fn(string $token) => $this->ratioOfNonLetterToWhole($token) < 0.8,
            )
        );
    }

    public function ratioOfNonLetterToWhole(string $text): float
    {
        return
            mb_strlen(preg_replace("/[\p{L}]/", '', $text)) /
            mb_strlen($text);
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

        if (count($this->tokens) > 0) {
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
        $pre_word = $pre_word !== null ? mb_strtolower($pre_word) : $pre_word;
        $post_word = $post_word !== null ? mb_strtolower($post_word) : $post_word;

        $most_similar_words = $this->getMostSimilarWords(
            $word,
            self::MAX_RECOMMENDATIONS
        );

        $report_text_file = new SplFileObject(base_path("report.txt"), "a");

        $report = function (string $text) use ($report_text_file) {
            $report_text_file->fwrite($text . "\n");
        };

        $dictionaryCount = Kata::query()->count();

        $report("Perhitungan rekomendasi untuk pembenaran ejaan kata \"{$word}\"\n");
        $report("Tahap 1: Mencari daftar kata termirip dengan \"{$word}\" dari {$dictionaryCount} kata pada lookup table dengan metode similaritas trigram.\ns");
        $report("Berikut merupakan perhitungan untuk " . self::MAX_RECOMMENDATIONS . " kata di lookup table dengan similaritas tertinggi dengan kata yang keliru ejaannya.\n");

        foreach ($most_similar_words as $most_similar_word) {
            $trigramA = json_decode($most_similar_word->trigram_huruf_kata_a);
            $trigramB = json_decode($most_similar_word->trigram_huruf_kata_b);

            $intersectionTrigrams = collect($trigramA)->intersect($trigramB);
            $unionTrigrams = collect($trigramA)->merge($trigramB)->unique();

            $report("Perhitungan similaritas trigram huruf antara \"{$word}\" (kata keliru) dan \"{$most_similar_word->word}\" (kata dari lookup table)");

            $report("Trigram huruf pada kata \"{$word}\" (kata dengan ejaan keliru): ");
            $report(join(", ", array_map(fn($text) => "\"{$text}\"", $trigramA)) . "\n");

            $report("Trigram huruf pada kata \"{$most_similar_word->word}\" (kata dari lookup table): ");
            $report(join(", ", array_map(fn($text) => "\"{$text}\"", $trigramB)) . "\n");

            $report("Trigram yang muncul di kedua kata: ");
            $report(join(", ", array_map(fn($text) => "\"{$text}\"", $intersectionTrigrams->toArray())));

            $report("Jumlah trigram pada kedua kata (tanpa menghitung yang ganda): " . $unionTrigrams->count());
            $report("Jumlah trigram yang muncul di kedua kata: " . $intersectionTrigrams->count());

            $report(sprintf(
                "Nilai similaritas untuk kedua kata: Jumlah trigram yang muncul di kedua kata / jumlah semua trigram di kedua kata =  %d/%d = %.2f\n",
                $intersectionTrigrams->count(),
                $unionTrigrams->count(),
                $intersectionTrigrams->count() / $unionTrigrams->count(),
            ));
        }

        $most_similar_words = $most_similar_words
            ->pluck("points", "word");

        $report("Langkah ke 2: Mengambil data frekuensi bigram-sebelum untuk masing-masing pasangan kata termirip dari tahap sebelumnya; Data bigram yang tidak ada dianggap nol: ");

        $pre_ngram_frequencies = FrekuensiNgram::query()
            ->where(["gram_1" => $pre_word])
            ->whereIn("gram_2", $most_similar_words->keys())
            ->pluck("frekuensi", "gram_2");

        if ($pre_ngram_frequencies->count() === 0) {
            $report("Tidak ada data frekuensi bigram-sebelum yang sesuai dengan seluruh pasangan kata dari tahap sebelumnya.\n");
        }

        foreach ($pre_ngram_frequencies as $candidateWord => $pre_ngram_frequency) {
            $pre_word ??= "NULL";
            $report("\"{$pre_word}\",  \"{$candidateWord}\" -> {$pre_ngram_frequency}");
        }

        $report("\nMembagi semua frekuensi dengan total frekuensi ({$pre_ngram_frequencies->sum()}) dan mengalikan dengan 100 supaya rentang nilai menjadi 0 - 1\n");

        $pre_ngram_frequencies = $pre_ngram_frequencies->mapWithKeys(function ($frequency, $word) use ($pre_ngram_frequencies) {
            return [$word => $frequency / $pre_ngram_frequencies->sum()];
        });

        foreach ($pre_ngram_frequencies as $candidateWord => $pre_ngram_frequency) {
            $pre_word ??= "NULL";
            $pre_ngram_frequency = number_format($pre_ngram_frequency, 2);
            $report("\"{$pre_word}\",  \"{$candidateWord}\" -> {$pre_ngram_frequency}");
        }

        $report("Langkah ke 3: Mengambil data frekuensi bigram-sesudah untuk masing-masing pasangan kata termirip dari tahap sebelumnya; Data bigram yang tidak ada dianggap nol: ");

        $post_ngram_frequencies = FrekuensiNgram::query()
            ->whereIn("gram_1", $most_similar_words->keys())
            ->where(["gram_2" => $post_word,])
            ->pluck("frekuensi", "gram_1");

        if ($post_ngram_frequencies->count() === 0) {
            $report("Tidak ada data frekuensi bigram-sesudah yang sesuai dengan seluruh pasangan kata dari tahap sebelumnya.\n");
        }

        foreach ($post_ngram_frequencies as $candidateWord => $post_ngram_frequency) {
            $post_word ??= "NULL";
            $report("\"{$candidateWord}\", \"{$post_word}\" -> {$post_ngram_frequency}");
        }

        $report("\nMembagi semua frekuensi dengan total frekuensi ({$post_ngram_frequencies->sum()}) dan mengalikan dengan 100 supaya rentang nilai menjadi 0 - 1");

        $post_ngram_frequencies = $post_ngram_frequencies->mapWithKeys(function ($frequency, $word) use ($post_ngram_frequencies) {
            return [$word => $frequency / $post_ngram_frequencies->sum()];
        });

        foreach ($post_ngram_frequencies as $candidateWord => $post_ngram_frequency) {
            $post_word ??= "NULL";
            $post_ngram_frequency = number_format($post_ngram_frequency, 2);
            $report("\"{$candidateWord}\", \"{$post_word}\" -> {$post_ngram_frequency}");
        }

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
            ->values();

        $report("Merata-ratakan nilai similaritas trigram huruf, frekuensi bigram awal, dan frekuensi bigram akhir untuk masing-masing rekomendasi, lalu dikalikan 100: \n");

        foreach ($results as $index => $result) {
            $iter = $index + 1;
            $report("{$iter}. Perhitungan untuk kata \"{$result['word']}\": \n");
            $report(sprintf("Similaritas Trigram Huruf => \"%0.2f\"", $result['similaritas_trigram_huruf']));
            $report(sprintf("Frekuensi Bigram Awal => \"%0.2f\"", $result['frekuensi_bigram_awal']));
            $report(sprintf("Frekuensi Bigram Akhir => \"%0.2f\"", $result['frekuensi_bigram_akhir']));
            $report(sprintf(
                "Rata-rata = ((%0.2f + %0.2f + %0.2f) / 3) * 100 = %0.2f\n",
                $result['similaritas_trigram_huruf'],
                $result['frekuensi_bigram_awal'],
                $result['frekuensi_bigram_akhir'],
                $result["points"] * 100
            ));
        }
        
        $results = $results->take(5);


        $report("Mengambil lima kata dengan nilai tertinggi: ");

        foreach ($results as $result) {
            $report(sprintf("\"%s\" => %.2f", $result["word"], $result["points"] * 100));
        }

        $report("--------------------------------------------------------------------------------------");

        return $results->toArray();
    }

    private function getMostSimilarWords(string $incorrectWord, int $max = 5): Collection
    {
        return Kata::query()
            ->select("isi AS word")
            ->selectRaw("similarity(isi, ?) AS points", [$incorrectWord])
            ->selectRaw("array_to_json(show_trgm(?)) AS trigram_huruf_kata_a", [$incorrectWord])
            ->selectRaw("array_to_json(show_trgm(isi)) AS trigram_huruf_kata_b")
            ->orderByRaw("similarity(isi, ?) DESC", [$incorrectWord])
            ->limit($max)
            ->get();
    }
}