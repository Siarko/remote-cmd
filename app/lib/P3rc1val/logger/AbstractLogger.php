<?php


namespace P3rc1val\logger;


use P3rc1val\logger\logFormatter\LogFormatterInterface;
use P3rc1val\logger\logFormatter\StandardLogFormatter;

abstract class AbstractLogger implements LoggerInterface {

    /* @var LogEntry*/
    protected $logs = [];
    protected $name = 'UNNAMED LOGGER';
    protected $fileName = null;
    protected $logger = null;
    protected $started = false;

    protected $logFormatter;

    public function __construct(Logger $logger) {
        $this->logFormatter = new StandardLogFormatter();
        $this->logger = $logger;
    }

    protected function startLog(){
        $line = '====== LOG {'.$this->name.'} STARTED ======';
        $this->addEntry(new LogEntry($line, self::LOG_RAW));
    }

    public function endLog() {
        $line = '====== LOG {'.$this->name.'} END ==========';
        $this->addEntry(new LogEntry($line, self::LOG_RAW));
    }

    protected function shouldSave(){
        return $this->logger->isEnabled($this->name);
    }

    public function setFileName(string $fileName) {
        $this->fileName = __APP_ROOT__.'/'.$fileName;
    }

    public function setLogFormatter(LogFormatterInterface $logFormatter){
        $this->logFormatter = $logFormatter;
    }

    protected function addEntry(LogEntry $entry){
        $this->logs[] = $entry;
    }

    public function setLoggerName(string $name) {
        $this->name = $name;
    }

    public function log(string $message, int $level = self::LOG_INFO) {
        if(!$this->started){
            $this->startLog();
            $this->started = true;
        }
        $this->addEntry(new LogEntry($message, $level));
    }
}