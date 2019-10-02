<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 12.08.2018
 * Time: 01:46
 */

namespace DbModelApi\traits;

trait SmartStaticProperties {
    protected static $_staticProperties = [];

    protected static function setStaticProp($name, $value){
        self::$_staticProperties[get_called_class()][$name] = $value;
    }

    protected static function getStaticProp($name){
        $class = get_called_class();
        if(
            array_key_exists($class, self::$_staticProperties) and
            array_key_exists($name, self::$_staticProperties[$class])
        ){
            return self::$_staticProperties[$class][$name];
        }else{
            return null;
        }
    }
}