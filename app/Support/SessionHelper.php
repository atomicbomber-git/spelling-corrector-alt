<?php


namespace App\Support;


class SessionHelper
{
    const MESSAGES_KEY = "messages";

    public static function flashMessage($content, $state)
    {
        session()->flash(self::MESSAGES_KEY, array_merge(session(self::MESSAGES_KEY) ?? [], [
            [
                "content" => $content,
                "state" => $state
            ]
        ]));
    }
}