<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.09.2019
 * Time: 16:03
 */

namespace P3rc1val\websocket;


use COM;
use P3rc1val\Config;

class WsProcess {

    private static function isWindows(){
        return (strtolower(explode(' ', php_uname())[0]) == 'windows');
    }

    /**
     * @return bool|int
     */
    public static function getState(){
        if(self::isWindows()){
            $cmd = 'wmic PROCESS WHERE NAME="php.exe" get Commandline,processid /VALUE';
            $result = trim(shell_exec($cmd), "\r\n");
            if(strlen($result) === 0){ return false; }
            if(strpos($result, "ws_server.php") === false){ return false; }
            $lines = explode('commandline=', strtolower($result));
            foreach ($lines as $line){
                if(strpos($line, 'ws_server.php') !== false){
                    return intval(explode('processid=', strtolower($line))[1]);
                }
            }
            return false;
        }else{
            $cmd = 'pgrep -f ws_server';
            $result = trim(shell_exec($cmd), "\n");
            if(strlen($result) === 0){ return false; }
            return intval($result);
        }
    }

    public static function kill($pid){
        if(self::isWindows()){
            $cmd = 'wmic process where processid='.$pid.' delete';
            return shell_exec($cmd);
        }else{
            $cmd = "php ".Config::WS_WORKER_SERVER." stop";
            return shell_exec($cmd);

        }
    }

    public static function start() {
        if(self::isWindows()){
            $cmd = "start /B php ".Config::WS_WORKER_SERVER." start -d";
            return pclose(popen($cmd, "r"));
        }else{
            $cmd = "php ".Config::WS_WORKER_SERVER." start -d > /dev/null &";
            return exec($cmd);
        }
    }

}