<?php


namespace App\Support;


use Carbon\Carbon;

class Formatter
{
    public static function formatDatetime($datetime): string
    {
        return Carbon::make($datetime)->format("m/d/Y H:i:s");
    }
}