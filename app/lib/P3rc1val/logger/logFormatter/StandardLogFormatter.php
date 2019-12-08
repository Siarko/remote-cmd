<?php


namespace P3rc1val\logger\logFormatter;


use P3rc1val\logger\LoggerInterface;

class StandardLogFormatter implements LogFormatterInterface {

    public function format(string $message, int $level): string {
        $result = '';
        if($level === LoggerInterface::LOG_RAW){
            $result = $message;
        }
        if($level === LoggerInterface::LOG_INFO){
            $result = '[INFO] '.$message;
        }
        if($level === LoggerInterface::LOG_WARN){
            $result = '[WARN] '.$message;
        }
        if($level === LoggerInterface::LOG_ERROR){
            $result = '[ERRO] '.$message;
        }

        return '[ '.date("Y-m-d H:i:s").' ] '.$result."\n";
    }
}