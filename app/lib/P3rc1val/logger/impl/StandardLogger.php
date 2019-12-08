<?php

namespace P3rc1val\logger\impl;

use P3rc1val\logger\AbstractLogger;
use P3rc1val\logger\LogEntry;

class StandardLogger extends AbstractLogger {

    public function save() {
        if(!$this->shouldSave()){ return; }

        /* @var LogEntry $item*/
        foreach ($this->logs as $item) {
            $line = $this->logFormatter->format($item->getMessage(), $item->getLevel());
            file_put_contents($this->fileName, $line, FILE_APPEND);
        }
    }
}