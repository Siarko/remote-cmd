<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 26.11.2019
 * Time: 21:19
 */

namespace frontend\actions;


use frontend\actions\dynamicObject\AbstractDynamicObject;
use frontend\actions\dynamicObject\ConstructableDynamicObject;
use P3rc1val\routing\RequestMethod;

abstract class AbstractActions {

    protected static $instance = null;

    /* @var AbstractDynamicObject */
    private $requestData = null;

    /* @var RequestMethod*/
    private $requestMethod = null;

    protected function getRequestData($key = null){
        if($key === null){ return $this->requestData; }
        return $this->requestData->$key;
    }

    protected function getRequestMethod(){
        return $this->requestMethod;
    }

    public static function __callStatic($name, $arguments) {
        return function (array $queryMatchParams, RequestMethod $requestMethod) use ($name, $arguments){
            return static::_callAction($name, $arguments, $queryMatchParams, $requestMethod);
        };
    }

    private static function _get() : self{
        if(static::$instance === null){
            static::$instance = new static();
        }
        return static::$instance;
    }

    private static function purifyQueryParamArray(array $queryArray){
        return array_filter($queryArray, function($key){
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    private static function _callAction(
        string $name, array $arguments, array $queryMatchParams, RequestMethod $requestMethod
    ){
        $methodName = 'action' . ucfirst($name);
        $queryMatchParams = self::purifyQueryParamArray($queryMatchParams);
        static::_get()->requestData = new ConstructableDynamicObject($queryMatchParams);
        static::_get()->requestMethod = $requestMethod;

        if(method_exists(static::_get(), $methodName)){
            return static::_get()->$methodName(...$arguments);
        }
        return null;
    }
}