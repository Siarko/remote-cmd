<?php


namespace P3rc1val\routing;


class RequestMethod {
    private $method;

    public function __construct($method) {
        $this->method = strtoupper($method);
    }

    private function compare($string){
        return $this->method === strtoupper($string);
    }

    public function isGet(){
        return $this->compare('get');
    }

    public function isPost(){
        return $this->compare('post');
    }

    public function __toString() {
        return $this->method;
    }
}