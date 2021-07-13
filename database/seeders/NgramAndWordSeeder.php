<?php

namespace Database\Seeders;

use App\FrekuensiNgram;
use App\Kata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class NgramAndWordSeeder extends Seeder
{
    private array $dictionary;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Kata::query()->forceDelete();
        FrekuensiNgram::query()->forceDelete();
        $this->loadKatas();
        $this->loadNgrams();
    }

    public function loadKatas(): void
    {
        $this->command->info("Loading data kata dari data/words.txt");

        Kata::query()->delete();

        $wordsFilePath = database_path("seeders/data/words.txt");
        $fileHandle = fopen($wordsFilePath, "r");

        $this->dictionary = [];

        while (($line = fgets($fileHandle)) !== false) {
            $this->dictionary[trim($line)] = true;
        }

        $beforeInsertionCount = Kata::query()->count();
        Kata::query()->insert(
            array_map(fn($word) => ["isi" => $word], array_keys($this->dictionary))
        );
        $insertedCount = Kata::query()->count() - $beforeInsertionCount;

        $this->command->info("Jumlah kata dari words.txt yang dimasukkan: " . number_format($insertedCount, 0, ',', '.'));
        fclose($fileHandle);
    }

    public function loadNgrams(): void
    {
        FrekuensiNgram::query()->delete();

        $sentence_files_root_dir = database_path("seeders/sentences");
        $sentence_filenames = scandir($sentence_files_root_dir);
        $ngram_frequencies = [];

        $this->command->line("Loading data dari file teks kalimat.");
        $file_loading_progress_bar = $this->createProgressBar($sentence_filenames);

        $extra_words = [];

        foreach ($sentence_filenames as $sentence_file) {
            if (in_array($sentence_file, [".", ".."])) {
                continue;
            }

            $file_loading_progress_bar->setMessage("Load berkas " . $sentence_file);
            $file_loading_progress_bar->advance();


            $filepath = "{$sentence_files_root_dir}/{$sentence_file}";
            $file_handle = fopen($filepath, "r");

            DB::beginTransaction();

            if ($file_handle) {
                while (($line = fgets($file_handle)) !== false) {
                    $line = preg_replace("/[\W_]/", " ", $line);
                    $line = preg_replace("/\s+/", " ", $line);
                    $line = trim($line);

                    $words = explode(' ', $line);
                    array_map(fn($word) => strtolower($word), $words);

                    foreach ($words as $word) {
                        if (!isset($this->dictionary[$word])) {
                            $extra_words[$word] = true;
                        }
                    }

                    for ($i = 0; $i < count($words) + 1; ++$i) {
                        $gram_1 = ($words[$i - 1] ?? null);
                        $gram_2 = ($words[$i - 0] ?? null);

                        $ngram_frequencies[$gram_1] ??= [];
                        $ngram_frequencies[$gram_1][$gram_2] ??= 0;
                        ++$ngram_frequencies[$gram_1][$gram_2];
                    }
                }
            } else {
                $this->command->error("Error opening {$filepath}.");
            }

            fclose($file_handle);
            DB::commit();
        }

        $extra_words = array_filter($extra_words, fn($extra_word) => strlen($extra_word) > 3, ARRAY_FILTER_USE_KEY);
        $extra_words = array_filter($extra_words, fn($extra_word) => strlen(preg_replace("/[^a-zA-Z]*/", '', $extra_word)) / strlen($extra_word) > 0.9, ARRAY_FILTER_USE_KEY);

        foreach ($extra_words as $word => $existence) {
            $this->dictionary[$word] = true;
        }

        $beforeInsertionCount = Kata::query()->count();
        Kata::query()->insert(array_map(fn($extra_word) => ["isi" => $extra_word], array_keys($extra_words)));
        $insertedCount = Kata::query()->count() - $beforeInsertionCount;

        $this->command->info("Jumlah kata di lookup table dari teks jurnal yang dimasukkan: " . number_format($insertedCount, 0, ',', '.'));
        $file_loading_progress_bar->finish();
        $flattened_ngram_frekuensi_values = $this->getFlattenedNgramFrequencyValues($ngram_frequencies);
        $this->storeNgramFrequenciesToDatabase($flattened_ngram_frekuensi_values);
    }

    /**
     * @param array $sentence_filenames
     * @return ProgressBar
     */
    public function createProgressBar(array $sentence_filenames): ProgressBar
    {
        $progress_bar = $this->command->getOutput()->createProgressBar(count($sentence_filenames));
        $progress_bar->setFormat("%current%/%max% [%bar%] %percent:3s%%\n%message%\n");
        return $progress_bar;
    }

    /**
     * @param array $ngram_frequencies
     * @return array
     */
    public function getFlattenedNgramFrequencyValues(array $ngram_frequencies): array
    {
        $flattened_ngram_frekuensi_values = [];
        foreach ($ngram_frequencies as $w1 => $gram_1_subs) {
            foreach ($gram_1_subs as $w2 => $frekuensi) {
                if (!isset(
                    $this->dictionary[$w1],
                    $this->dictionary[$w2],
                )) {
                    continue;
                }

                $flattened_ngram_frekuensi_values[] = [
                    "gram_1" => $w1 != "" ? $w1 : null,
                    "gram_2" => $w2 != "" ? $w2 : null,
                    "frekuensi" => $frekuensi,
                ];
            }
        }
        return $flattened_ngram_frekuensi_values;
    }

    /**
     * @param array $flattened_ngram_frekuensi_values
     */
    public function storeNgramFrequenciesToDatabase(array $flattened_ngram_frekuensi_values): void
    {
        $chunk_size = 2000;
        $frekuensi_chunk_list = array_chunk($flattened_ngram_frekuensi_values, $chunk_size);
        $database_load_progressbar = $this->createProgressBar($frekuensi_chunk_list);

        foreach (array_chunk($flattened_ngram_frekuensi_values, $chunk_size) as $index => $frekuensi_chunks) {
            $database_load_progressbar->setMessage(sprintf(
                "Memasukkan data ke %d-%d.",
                $index * $chunk_size + 1,
                ($index + 1) * $chunk_size,
            ));

            FrekuensiNgram::query()->insert(
                $frekuensi_chunks
            );

            $database_load_progressbar->advance();
        }

        $database_load_progressbar->finish();
    }
}
