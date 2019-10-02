<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.10.2018
 * Time: 22:26
 */

namespace P3rc1val;


class Exceptions {
    public static function error($message){
        echo('[ERROR] '.$message);
        exit();
    }

}