<?php


namespace P3rc1val\logger;


class LogEntry {

    private $level;
    private $message;

    public function __construct(string $message, int $level) {
        $this->message = $message;
        $this->level = $level;
    }

    /**
     * @return int
     */
    public function getLevel(): int {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

}