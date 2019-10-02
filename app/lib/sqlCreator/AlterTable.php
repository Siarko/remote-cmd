<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 08.05.2019
 * Time: 13:57
 */

namespace sqlCreator;

class AlterTable extends \sqlCreator\BasicQuery {

    private $tableName = null;

    private $addColumns = [];

    function __construct($name) {
        $this->tableName = $name;
    }

    public function add(\sqlCreator\databaseElement\Column $columnData){
        $this->addColumns[] = $columnData;
    }

    /**
     * @return string
     */
    public function parse() {
        $text = 'ALTER TABLE '.$this->tableName.' ';

        /* @var \sqlCreator\databaseElement\Column $column*/
        $i = count($this->addColumns);
        foreach ($this->addColumns as $column) {
            $text .= 'ADD ';
            $i--;
            $text .= $column->__toString();
            if($i > 0){
                $text .= ',';
            }
            $text .= ' ';
        }

        return $text;
    }
}