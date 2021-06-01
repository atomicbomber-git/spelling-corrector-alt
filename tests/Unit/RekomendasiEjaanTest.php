<?php


function getLetterNgrams(string $text, int $ngram = 3): array {
    $results = [];
    $text = str_split($text);

    for ($i = 0; $i < count($text) + ($ngram - 1); ++$i) {
        $temp = ($text[$i - 2] ?? "");
        $temp .= ($text[$i - 1] ?? "");
        $temp .= ($text[$i - 0] ?? "");
        $results[] = $temp;
    }

    return $results;
}

function trigramSimilarity(string $text1, string $text2): float {
    $ngram_of_text1 = getLetterNgrams($text1);
    $ngram_of_text2 = getLetterNgrams($text2);

    $intersect_count = collect($ngram_of_text1)->intersect($ngram_of_text2)->count();
    $total = collect($ngram_of_text1)->merge($ngram_of_text2)->unique()->count();

    return $intersect_count / $total;
}

test("Test", function () {
    dump(
        DB::unprepared("SELECT show_tgrm('pneumonia')")
    );
    
    
//    "pneumonia" => "0.14285715"






//    $rawString = "bahwa pnbrn kawasan adlh lebih baik";
//    $tokens = explode(' ', $rawString);
//
//    $rekomendatorKoreksiEjaan = new \App\Tools\RekomendatorKoreksiEjaan($tokens);
//    $rekomendatorKoreksiEjaan->recommendations();
});