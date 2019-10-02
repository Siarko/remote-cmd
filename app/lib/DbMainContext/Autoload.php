<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 01.10.2018
 * Time: 22:31
 */

spl_autoload_register(function($name){

    $ex = explode(DIRECTORY_SEPARATOR, __DIR__);
    $MODULE_NAME = $ex[count($ex)-1];
    $name .= '.php';

    $result = strpos($name, $MODULE_NAME);
    if(defined('AUTOLOAD_DEBUG')){
        if($result !== false){
            echo("[".$MODULE_NAME."] LOADING CLASS ".$name."<br/>");
        }
    }
    if($result === FALSE){return;}

    $name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
    $name = substr($name, strlen($MODULE_NAME));
    $path = __DIR__.$name;

    if(file_exists($path)){
        require_once($path);
    }else{
        echo("Cannot load file for module ".$MODULE_NAME.": ".$name.' in '.$path.'<br/>');
        exit();
    }
});