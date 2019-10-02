<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.04.2019
 * Time: 23:10
 */

namespace sqlCreator\databaseElement;


class Column {

    const SPECIAL = ['CURRENT_TIMESTAMP', 'NULL'];

    private $name;
    private $type;
    private $keyType = null;
    private $nullable = false;
    private $autoIncrement = false;
    private $defaultValue = null;

    function __construct($name) {
        $this->name = $name;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function isKey($keyType){
        $this->keyType = $keyType;
    }

    public function nullable($flag){
        $this->nullable = $flag;
    }

    public function autoIncrement($flag){
        $this->autoIncrement = $flag;
    }

    public function defaultValue($value){
        $this->defaultValue = $value;
    }

    function __toString() {
        $str = $this->name.' '.$this->type;
        if($this->keyType != null){
            $str .= ' '.$this->keyType.' KEY';
        }
        if($this->autoIncrement){
            $str .= ' AUTO_INCREMENT';
        }
        if($this->defaultValue !== null){
            $str .= ' DEFAULT ';
            if(gettype($this->defaultValue) =='string'){
                if(in_array($this->defaultValue, Column::SPECIAL)){
                    $str .= $this->defaultValue;
                }else{
                    $str .= "'".$this->defaultValue."'";
                }
            }else{
                $str .= $this->defaultValue;
            }
        }else{
            if(!$this->nullable){
                $str .= ' NOT NULL';
            }else{
                $str .= ' NULL';
            }
        }

        return $str;
    }

}