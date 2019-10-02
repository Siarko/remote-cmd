<?php


/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 10.08.2018
 * Time: 17:35
 */

namespace sqlCreator;

class Select extends \sqlCreator\BasicQuery {

    public $selectedColumns = [];

    private function addColumn($columnName){
        if(!in_array($columnName, $this->selectedColumns)){
            $this->selectedColumns[] = $columnName;
        }
    }

    /**
     * Select constructor.
     * @param string[] $columns
     */
    function __construct($columns = []) {
        $this->parseArgument($columns, function($column){
            if(is_string($column)){
                $this->addColumn($column);
            }
        });
    }

    public function from($tableNames){
        /* @var $this \sqlCreator\BasicQuery*/
        $this->parseArgument($tableNames, function($tableName){
            $this->addTable($tableName);
        });

        return new \sqlCreator\SelectiveQuery($this);
    }


    /**
     * @return string
     */
    public function parse(){return null;}
}