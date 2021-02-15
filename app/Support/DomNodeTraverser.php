<?php


namespace App\Support;

use DOMNode;


class DomNodeTraverser
{
    public static function traverse(DOMNode $node, Callable $callable)
    {
        $callable($node);

        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                self::traverse($childNode, $callable);
            }
        }
    }
}