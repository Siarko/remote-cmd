<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.04.2019
 * Time: 23:07
 */

namespace sqlCreator;


use sqlCreator\databaseElement\Column;

class CreateTableQuery {

    private $tableName = null;
    private $columns = [];

    function __construct($tableName) {
        $this->tableName = $tableName;
    }

    public function column($column){
        $this->columns[] = $column;
        return $this;
    }

    private function getColumns(){
        $ret = '';
        $i = 0;
        /* @var Column $column*/
        foreach ($this->columns as $column) {
            $ret .= $column;
            if($i < count($this->columns)-1){
                $ret .= ', ';
            }
            $i++;
        }
        return $ret;
    }

    public function parse(){
        return 'CREATE TABLE '.$this->tableName.'('.$this->getColumns().');';
    }

    function __toString() {
        return $this->parse();
    }
}