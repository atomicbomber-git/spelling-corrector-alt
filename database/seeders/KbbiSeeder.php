<?php


namespace Database\Seeders;


use App\Kata;
use Illuminate\Database\Seeder;
use Str;

class KbbiSeeder extends Seeder
{
    public function run()
    {
        $this->insertFromFirstSource();
    }

    private function insertFromFirstSource(): void
    {
        $fileHandle = fopen(__DIR__ . "/kata_dasar_kbbi.csv", "r");

        $wordList = [];
        while (($line = fgetcsv($fileHandle)) !== false) {
            if (str_starts_with($line[0], '-') || str_ends_with($line[0], '-')) {
                continue;
            }

            $wordList[] = ["isi" => $line[0]];
        }

        fclose($fileHandle);

        $count = Kata::query()->insertOrIgnore($wordList);
        $this->command->info("Jumlah kata dari KBBI: " . number_format($count, 0, '.', ','));
    }
}