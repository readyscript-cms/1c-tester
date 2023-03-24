<?php


namespace OneCTester\utils;


class ResponseSplit
{
    public static function split(string $string): array
    {
        return preg_split("/\r\n|\n|\r/", $string);
    }
}