<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 29.08.2019
 * Time: 18:57
 */

namespace P3rc1val\websocket;


use DbMainContext\DbContext;
use P3rc1val\database\models\Hosts;

class HostManagement {

    public static function resetAllHostStates(){
        mylog("Reseting all hosts states");
        $hosts = Hosts::find()->all();
        /* @var $host Hosts*/
        foreach ($hosts as $host) {
            mylog("[RESET] -> ".$host->hostname." [".$host->id."]");
            $host->host_ready = 0;
            $host->user = null;
            if(!$host->save()){
                mylog("Host reset failed: ".$host->hostname, true);
                mylog(DbContext::getError(), true);
            }
        }
        mylog("All host reset complete");
    }

    public static function authorizeManagement($hostId, $username){
        /* @var $host Hosts*/
        $host = Hosts::find($hostId)->one();
        if($host){
            $host->user = $username;
            return $host->save();
        }
        return null;
    }

    public static function disconnectUser($username){
        /* @var $host Hosts*/
        $host = Hosts::find([Hosts::$USER => $username])->one();
        if($host){
            $host->user = null;
            $r = $host->save();
            $r2 = self::disconnectHost($host->id);
            if(!$r2){ return false;}
            return $r;
        }
        return null;
    }

    public static function disconnectHost($hostId){
        /* @var $host Hosts*/
        $host = Hosts::find($hostId)->one();
        if($host){
            $host->host_ready = 0;
            $r = $host->save();
            RequestHandler::notifyHost($hostId, 'user_disconnect', []);
            return $r;
        }
        return null;
    }

    public static function getConnectedHostId($userName){
        /* @var $host Hosts|null*/
        $host = Hosts::find([Hosts::$USER => $userName])->one();
        if($host){
            return $host->id;
        }
        return null;
    }

    public static function getConnectedUsername($hostId){
        /* @var $host Hosts|null*/
        $host = Hosts::find($hostId)->one();
        if($host){
            return $host->user;
        }
        return null;
    }

}