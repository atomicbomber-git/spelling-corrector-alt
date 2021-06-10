<?php


namespace Database\Seeders;


use App\Kata;
use Illuminate\Database\Seeder;
use Str;

class ExtraCorpusSeeder extends Seeder
{
    public function run()
    {
        $file = new \SplFileObject(__DIR__ . "/data/ind_mixed_2012_1M-inv_w.txt");

        $dictionary = [];

        // Loop until we reach the end of the file.
        while (!$file->eof()) {
            $parts = explode("\t", $file->fgets());

            if (count($parts) !== 3) continue;

            $content = $parts[1];
            $content = preg_replace("/^[^\p{L}]/ui", '', $content);
            $content = preg_replace("/[^\p{L}]$/ui", '', $content);
            $words = preg_split("/[- ]/ui", $content);

            foreach ($words as $word) {
                $word = mb_strtolower($word);
                $dictionary[$word] = true;
            }
        }

        foreach (array_chunk(array_keys($dictionary), 10_000) as $dictionary_chunk) {
            $count = Kata::query()->insertOrIgnore(
                array_map(fn($word) => ["isi" => $word], $dictionary_chunk)
            );
            
            $this->command->line("Berhasil memasukkan {$count} kata...");
        }
    }
}