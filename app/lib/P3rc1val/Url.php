<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 02.10.2018
 * Time: 19:19
 */

namespace P3rc1val;


use P3rc1val\routing\Router;

class Url {
    public static function getPrefix(){
        $domain = "http://".$_SERVER['HTTP_HOST'];
        $subFolder = substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME'])-9);
        return $domain.$subFolder;
    }

    public static function getSuffix(){
        if(!array_key_exists('url', $_GET)){
            return '';
        }
        return $_GET['url'];
    }

    public static function getMethod(){
        if(count($_POST) > 0 or $_SERVER['REQUEST_METHOD'] == 'POST'){
            return Router::POST;
        }
        return Router::GET;
    }

    public static function redirect($url){
        header("Location: ".self::getPrefix().$url);
    }
}