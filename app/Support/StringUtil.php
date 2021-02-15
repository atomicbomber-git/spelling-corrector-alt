<?php


namespace App\Support;


use Illuminate\Support\Collection;

class StringUtil
{
    public static function replaceAllRegex(string $pattern, string $replacement, string $subject): string {
        $matches = [];
        preg_match($pattern, $subject, $matches);

        while ($matches !== []) {
            $subject = preg_replace($pattern, $replacement, $subject);
            preg_match($pattern, $subject, $matches);
        }

        return $subject;
    }

    public static function replaceAllRegexMultiple(string $pattern, array $replacements, string $subject): string {
        $replacements = (new Collection($replacements))
            ->pluck("correction", "index")
            ->toArray();

        $indexCounter = 0;
        $matches = [];
        preg_match($pattern, $subject, $matches);

        while (($matches !== []) && ($indexCounter < (array_key_last($replacements) + 1))) {
            if (isset($replacements[$indexCounter])) {
                $subject = preg_replace($pattern, $replacements[$indexCounter], $subject, 1);
            }

            preg_match($pattern, $subject, $matches);

            ++$indexCounter;
        }

        return $subject;
    }
}