<?php


namespace OneCTester\utils;

class Logger
{
    public static function log(string $string): void
    {
        echo '['.date('H:i:s').'] [1CTester] ' . $string . PHP_EOL;
    }
}