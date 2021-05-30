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

        $file = new \SplFileObject(__DIR__ . "/data/in");

        // Loop until we reach the end of the file.
        while (!$file->eof()) {
            $line = trim($file->fgets());

        }

        // Unset the file to call __destruct(), closing the file handle.
        $file = null;
    
    
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

        Kata::query()->insertOrIgnore($wordList);
    }
}