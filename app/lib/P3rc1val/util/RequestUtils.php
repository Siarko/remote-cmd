<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 28.12.2018
 * Time: 18:19
 */

namespace P3rc1val\util;

class RequestUtils {
    public static function allInArray($array, ...$names){
        foreach ($names as $name) {
            if(!array_key_exists($name, $array)){
                return false;
            }
        }
        return true;
    }
}