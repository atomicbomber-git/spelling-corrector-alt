<?php


namespace App\Support;


use DOMNode;
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

    public static function replaceAllRegexMultipleInXmlNode(string $pattern, array $replacements, DOMNode $DOMNode): DOMNode {
        $replacements = (new Collection($replacements))
            ->pluck("correction", "index")
            ->toArray();

        $indexCounter = 0;
        DomNodeTraverser::traverse($DOMNode, function (DOMNode $currentNode) use ($pattern, $replacements, &$indexCounter) {
            if ($currentNode->nodeType !== XML_TEXT_NODE) return;


            $subject = $currentNode->wholeText;



            /* Replace anything found in $subject */
            $matches = [];
            preg_match($pattern, $subject, $matches);


            $check = str_contains(strtolower($subject), "apabila");
            if ($check) {
                ray()->send([$pattern, $subject, $matches]);
            }

            while (($matches !== []) && ($indexCounter < (array_key_last($replacements) + 1))) {
                if (isset($replacements[$indexCounter])) {
                    $subject = preg_replace($pattern, $replacements[$indexCounter], $subject, 1);
                }
                preg_match($pattern, $subject, $matches);
                ++$indexCounter;
            }

            if ($check) {
                ray()->send($subject);
            }

            $currentNode->parentNode->replaceChild(
                $currentNode->ownerDocument->createTextNode($subject),
                $currentNode,
            );
        });

        return $DOMNode;
    }
}