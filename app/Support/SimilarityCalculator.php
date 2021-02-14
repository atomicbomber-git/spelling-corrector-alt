<?php


namespace App\Support;


class SimilarityCalculator
{
    public static function jaroDistance(string $textA, string $textB): float
    {
        list($shorter, $longer) = strlen($textA) <= strlen($textB) ?
            [$textA, $textB] :
            [$textB, $textA];

        $getMatchedCharacters = function ($textA, $textB) {
            $limit = floor(strlen($textA) / 2);
            $matched = "";

            for ($i = 0; $i < strlen($textA); ++$i) {

                $leftBound = max(0, $i - $limit);
                $rightBound = min($i + $limit + 1, strlen($textB));

                $searchTarget = substr($textB, $leftBound, $rightBound - $leftBound);

                $pos = strpos($searchTarget, $textA[$i]);

                if ($pos !== false) {
                    $matched .= $textA[$i];
                    $textB = substr($textB, 0, $pos) . " " . substr($textB, $pos + 1, strlen($textB));
                }
            }

            return $matched;
        };

        $matching1 = $getMatchedCharacters($shorter, $longer);
        $matching2 = $getMatchedCharacters($longer, $shorter);
        $matchCount = strlen($matching1);
        $shorterMatchingLength = min(strlen($matching1), strlen($matching2));

        $transpositionCount = 0;
        for ($i = 0; $i < $shorterMatchingLength; ++$i) {
            $transpositionCount += ($matching1[$i] !== $matching2[$i]) ?
                1 : 0;
        }
        $transpositionCount = floor($transpositionCount / 2);


        if ($matchCount === 0) {
            return 0.0;
        } else {
            return 1.0 / 3.0 * (
                    $matchCount / strlen($textA) +
                    $matchCount / strlen($textB) +
                    ($matchCount - $transpositionCount) / $matchCount
                );
        }
    }

public static function jaroWinklerSimilarity(string $textA, string $textB, float $scalingFactor = 0.1): float
    {
        $jaroDistance = self::jaroDistance($textA, $textB);

        return $jaroDistance +
            strlen(self::commonPrefix($textA, $textB)) * $scalingFactor * (1 - $jaroDistance);
    }

    public static function commonPrefix(string $textA, string $textB, $max=4): string
    {
        $shorterLength = min(strlen($textA), strlen($textB));

        $commonPrefix = "";
        for ($i = 0; $i < $shorterLength; ++$i) {
            if ($i >= $max) {
                break;
            }

            if ($textA[$i] === $textB[$i]) {
                $commonPrefix .= $textA[$i];
            }
            else {
                break;
            }
        }

        return $commonPrefix;
    }
}
