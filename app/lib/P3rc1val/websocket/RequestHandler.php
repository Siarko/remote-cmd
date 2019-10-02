<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 29.08.2019
 * Time: 13:45
 */

namespace P3rc1val\websocket;

use P3rc1val\database\models\Hosts;
use P3rc1val\util\ArrayUtils;
use Workerman\Connection\TcpConnection;

class RequestHandler {

    private static $handlers = [];

    public static function registerUserHandler($actionName, $action){
        self::$handlers["u_".$actionName] = $action;
    }

    public static function registerHostHandler($actionName, $action){
        self::$handlers["h_".$actionName] = $action;
    }

    public static function registerUserPassthrough($name){
        self::registerUserHandler($name, function($data) use ($name){
            self::notifyHost(
                HostManagement::getConnectedHostId($_SESSION['login']),
                $name, $data
            );
            return null;
        });
    }

    public static function registerHostPassthrough($name){
        self::registerHostHandler($name, function($hostId, $data) use ($name){
            self::notifyUser(
                HostManagement::getConnectedUsername($hostId),
                $name, $data
            );
            return null;
        });
    }

    public static function formatNotification($data, $name){
        return json_encode([
            'type' => 'notification',
            'name' => $name,
            'payload' => $data
        ]);
    }

    public static function formatResponse($data, $name = null, $id = null){
        return json_encode([
            'type' => 'response',
            'name' => $name,
            'data' => [
                'id' => $id
            ],
            'payload' => $data
        ]);
    }

    public static function isHostRequest($data){
        return ArrayUtils::existAndEquals('client_type', $data, 'host');
    }

    public static function restoreSession($data){
        if(array_key_exists('PHPSESSID', $data)){
            session_id($data['PHPSESSID']);
            @session_start();
            session_write_close();
        }
    }

    public static function handle(TcpConnection $connection, $data){
        $actionName = 'u_'.$data['name'];
        if(self::isHostRequest($data)) {
            $actionName = 'h_'.$data['name'];
        }
        $id = null;
        if(array_key_exists('type', $data) and $data['type'] == 'request'){
            $id = $data['data']['id'];
        }
        if(!array_key_exists($actionName, self::$handlers)){
            $connection->send(self::formatResponse([
                'error' => 'requested action not found',
                'debug' => [
                    'action' => $actionName
                ]
            ], $data['name'], $id));
            return;
        }
        $action = self::$handlers[$actionName];
        $params = [];
        if(array_key_exists('payload', $data)){
            $params = $data['payload'];
        }
        if(self::isHostRequest($data)){
            $resp = $action($data['hostid'],$params);
        }else{
            $resp = $action($params);
        }
        if($resp !== null){
            $connection->send(self::formatResponse($resp, $data['name'], $id));
        }

    }

    public static function notifyUser($userName, $notificationName, $data){
        $message = self::formatNotification($data, $notificationName);
        $userId = ConnectionRegistry::getIdByUser($userName);
        /* @var $connection TcpConnection*/
        $connection = ConnectionRegistry::getUserConnection($userId);
        if($connection == null){
            return false;
        }else{
            $connection->send($message);
        }
        return true;
    }
    public static function notifyHost($hostId, $notificationName, $data){
        $message = self::formatNotification($data, $notificationName);
        $hostConId = ConnectionRegistry::getIdByHost($hostId);
        /* @var $connection TcpConnection*/
        $connection = ConnectionRegistry::getHostConnection($hostConId);
        if($connection == null){
            return false;
        }else{
            $connection->send($message);
            return true;
        }
    }

    public static function validateConnection($data) {
        if(ArrayUtils::existAndEquals('client_type', $data, 'client') ){
            self::restoreSession($data);
            return array_key_exists('login', $_SESSION);
        }
        if(ArrayUtils::existAndEquals('client_type', $data, 'host') ){
            return true;
        }
        return false;
    }
}