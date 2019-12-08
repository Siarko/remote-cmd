<?php


namespace P3rc1val\util;


abstract class SingletonType {

    protected static $_instance = null;

    protected function __construct() {}

    /**
     * @return static
     */
    public static function getInstance() {
        if(static::$_instance === null){
            static::$_instance = new static();
        }
        return static::$_instance;
    }

}