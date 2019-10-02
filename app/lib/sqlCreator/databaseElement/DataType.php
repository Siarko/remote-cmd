<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.04.2019
 * Time: 23:16
 */

namespace sqlCreator\databaseElement;


class DataType {
    private $name;
    private $length;

    function __construct($name, $length) {
        $this->name = $name;
        $this->length = $length;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getLength() {
        return $this->length;
    }

    public function __toString() {
        return $this->name.(($this->length !== null)?'('.$this->length.')':'');
    }


}