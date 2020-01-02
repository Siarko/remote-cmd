<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.10.2018
 * Time: 19:13
 */

namespace DbMainContext;

use mysqli;
use P3rc1val\Deployment;

class DbContext {

    /* @var \mysqli $context*/
    private static $context = null;

    private static function mysqliCheck(){
        if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
            echo 'No mysqli installed !!!';
            exit();
        }
    }

    public static function init() {
        if(!self::$context){
            self::mysqliCheck();
            self::$context = new mysqli(
                Deployment::DATABASE['host'],
                Deployment::DATABASE['user'],
                Deployment::DATABASE['password'],
                Deployment::DATABASE['database']
            );
            if(mysqli_connect_errno()){
                echo("MYSQL ERROR - ".mysqli_connect_error());
                exit();
            }
        }
    }

    public static function getContext(){
        self::init();
        return self::$context;
    }

    public static function query($sql){
        self::init();
        if(!self::$context->ping()){
            self::$context = false;
            self::init();
        }
        return self::$context->query($sql);
    }

    /**
     * @param $result \mysqli_result
     * @param $mode int
     */
    public static function fetchAll($result, $mode){
        if(method_exists('mysqli_result', 'fetch_all')){
            return $result->fetch_all($mode);
        }else{
            for ($res = array(); $tmp = $result->fetch_array($mode);){
                $res[] = $tmp;
            }
            return $res;
        }
    }

    public static function getError(){
        return self::$context->error;
    }

    public static function getErrorNo(){
        return self::$context->errno;
    }

    public static function getInsertedId(){
        return self::$context->insert_id;
    }

}