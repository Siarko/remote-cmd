<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 07.09.2019
 * Time: 16:09
 */

namespace P3rc1val\websocket;


class StaticRegistry {

    public static $lastReceivedFrame = null;

    public static function receiveFrame(){
        if(self::$lastReceivedFrame === null){
            self::$lastReceivedFrame = microtime(true);
            return 0;
        }
        $dif = microtime(true)-self::$lastReceivedFrame;
        self::$lastReceivedFrame = microtime(true);
        return $dif;
    }

    public $streamStarted = false;
    public $streamClientNotified = false;

}