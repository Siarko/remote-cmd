<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 10.08.2018
 * Time: 20:39
 */

namespace P3rc1val\util;

class HtmlElement {

    public $_name;
    public $value = null;

    private $_attributes = [];

    function __construct($name, $value = null) {
        $this->_name = $name;
        $this->value = $value;
    }

    function __set($name, $value) {
        $this->_attributes[$name] = $value;
    }

    function __toString() {
        $atributeString = $this->getAttrString($this->_attributes);
        $element = "<" . $this->_name . " " . $atributeString . ">" . $this->value . "</" . $this->_name . ">";
        return $element;
    }

    private function getAttrString($attrArray) {
        $attributeString = '';
        foreach ($attrArray as $n => $v) {
            $attributeString .= $n . "='" . $v . "' ";
        }
        return $attributeString;
    }
}