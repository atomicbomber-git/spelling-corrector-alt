<?php


test("Test", function () {
    $rawString = "bahwa pnbrn kawasan adlh lebih baik";
    $tokens = explode(' ', $rawString);

    $rekomendatorKoreksiEjaan = new \App\Tools\RekomendatorKoreksiEjaan($tokens);
    $rekomendatorKoreksiEjaan->recommendations();
});