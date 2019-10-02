<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2018
 * Time: 23:41
 */

namespace sqlCreator;

use MultipleUpdateCollumns;

class Update extends ConditionedQuery {

    protected $changes = [];

    function __construct($tables) {
        $this->parseArgument($tables, function($tableName){
            $this->addTable($tableName);
        });
        return $this;
    }

    /**
     * $columns = ['kolumna1', 'kolumna2']
     * $values = ['wartosc1', 'wartosc2']
     *
     * $columns = [ 'kolumna1' => 'wartosc', 'kolumna2' => 'wartosc2']
     * @param $columns array
     * @param array|null $values
     * @return $this
     * @throws MultipleUpdateCollumns
     */
    public function set($columns, $values = null){
        if(is_array($values)){
            foreach ($columns as $key => $column) {
                if(array_key_exists($column, $this->changes)){
                    throw new MultipleUpdateCollumns();
                }
                $this->changes[$column] = $values[$key];
            }
        }else{
            $this->changes = $columns;
        }

        return $this;
    }

    public function parse() {
        $sql = 'UPDATE ';
        $sql .= $this->arrayToList($this->tables);
        if(count($this->changes) != 0){
            $sql .= 'SET ';
            $sql .= $this->createSetString();
        }
        if(!$this->isConditionEmpty()){
            $sql .= $this->createConditionString();
        }
        return $sql;
    }

    private function createSetString() {
        $sql = '';
        $index = 0;
        foreach ($this->changes as $column => $value) {
            $v = ($value == null?'NULL':'\''.$value.'\'');
            $sql .= $column.'='.$v;
            if($index < count($this->changes)-1){
                $sql .= ', ';
            }
            $index++;
        }
        return $sql.' ';
    }
}