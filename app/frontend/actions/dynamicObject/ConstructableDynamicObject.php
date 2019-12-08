<?php


namespace frontend\actions\dynamicObject;


class ConstructableDynamicObject extends AbstractDynamicObject {

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }
}