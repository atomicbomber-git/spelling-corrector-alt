<?php


namespace Database\Seeders;


use App\Kata;
use Illuminate\Database\Seeder;

class KbbiSeeder extends Seeder
{
    public function run()
    {
        $fileHandle = fopen(__DIR__ . "/kata_dasar_kbbi.csv", "r");

        $wordList = [];
        while (($line = fgetcsv($fileHandle)) !== false) {
            $wordList[] = ["isi" => $line[0]];
        }

        fclose($fileHandle);

        Kata::query()->insertOrIgnore($wordList);
    }
}