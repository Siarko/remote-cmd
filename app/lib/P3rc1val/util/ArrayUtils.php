<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2019
 * Time: 15:34
 */

namespace P3rc1val\util;


class ArrayUtils {
    public static function requireKeys($array, ...$keys){
        foreach ($keys as $key) {
            if(!array_key_exists($key, $array)){
                return false;
            }
        }
        return true;
    }

    public static function existAndEquals($key, $array, $value){
        return (array_key_exists($key, $array) and $array[$key] === $value);
    }
}