<?php


namespace P3rc1val\logger\logFormatter;

interface LogFormatterInterface {

    public function format(string $message, int $level) : string;

}