<?php


namespace App\Support;


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
}