<?php

namespace P3rc1val\logger;

interface LoggerInterface {

    const LOG_INFO = 0;
    const LOG_WARN = 1;
    const LOG_ERROR = 2;
    const LOG_RAW = 3;

    public function setLoggerName(string $name);

    public function setFileName(string $fileName);

    public function log(string $message, int $level = self::LOG_INFO);

    public function endLog();

    public function save();
}