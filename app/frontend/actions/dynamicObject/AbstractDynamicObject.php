<?php

namespace frontend\actions\dynamicObject;

abstract class AbstractDynamicObject {

    private $__fields = [];

    public function __get($name) {
        if(array_key_exists($name, $this->__fields)){
            return $this->__fields[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        $this->__fields[$name] = $value;
    }
}