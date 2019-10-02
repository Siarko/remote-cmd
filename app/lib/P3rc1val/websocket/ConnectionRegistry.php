<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 29.08.2019
 * Time: 21:03
 */

namespace P3rc1val\websocket;


use Workerman\Connection\TcpConnection;

class ConnectionRegistry {

    const USER = 0;
    const HOST = 1;

    //static registry intances - same for host and client connections
    private static $staticRegistries = [];

    //indexed by connection id
    private static $userConnections = [];
    private static $hostConnections = [];

    //indexed by username/hostid
    private static $userRegistry = [];
    private static $hostRegistry = [];

    public static function registerConnection($type, $id, $name, TcpConnection $connection){
        if($type == ConnectionRegistry::USER){
            if(array_key_exists($id, self::$userConnections)){
                return;
            }
            self::$userConnections[$id] = $connection;
            self::$userRegistry[$name] = $id;

            self::$staticRegistries[$id] = new StaticRegistry();
        }

        if($type == ConnectionRegistry::HOST){
            if(array_key_exists($id, self::$hostConnections)){
                return;
            }
            self::$hostConnections[$id] = $connection;
            self::$hostRegistry[$name] = $id;
        }
    }

    /**
     * @param $userId
     * @return StaticRegistry|null
     */
    public static function getStaticRegistry($userId){
        if(is_string($userId)){
            $userId = ConnectionRegistry::getIdByUser($userId);
        }
        if(array_key_exists($userId, self::$staticRegistries)){
            return self::$staticRegistries[$userId];
        }
        return null;
    }

    /**
     * @param $id
     * @return TcpConnection|null
     */
    public static function getUserConnection($id){
        if(array_key_exists($id, self::$userConnections)){
            return self::$userConnections[$id];
        }
        return null;
    }

    /**
     * @param $id
     * @return TcpConnection|null
     */
    public static function getHostConnection($id){
        if(array_key_exists($id, self::$hostConnections)){
            return self::$hostConnections[$id];
        }
        return null;
    }

    public static function getIdByUser($userName){
        if(array_key_exists($userName, self::$userRegistry)){
            return self::$userRegistry[$userName];
        }
        return null;
    }
    public static function getIdByHost($hostId){
        if(array_key_exists($hostId, self::$hostRegistry)){
            return self::$hostRegistry[$hostId];
        }
        return null;
    }

    public static function getUserById($id){
        return array_search($id, self::$userRegistry);
    }

    public static function getHostById($id){
        return array_search($id, self::$hostRegistry);
    }

    public static function flush($id){
        if(array_key_exists($id, self::$hostConnections)){
            unset(self::$hostConnections[$id]);

            if (($key = array_search($id, self::$hostRegistry)) !== false) {
                unset(self::$hostRegistry[$key]);
            }
        }

        if(array_key_exists($id, self::$userConnections)){
            unset(self::$userConnections[$id]);

            if (($key = array_search($id, self::$userRegistry)) !== false) {
                unset(self::$userRegistry[$key]);
            }
            unset(self::$staticRegistries[$id]);
        }
    }

    public static function isHost($id){
        return (array_key_exists($id, self::$hostConnections));
    }

}