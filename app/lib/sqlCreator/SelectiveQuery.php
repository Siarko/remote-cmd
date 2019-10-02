<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 10.08.2018
 * Time: 17:34
 */

namespace sqlCreator;

use ReflectionClass;
use sqlCreator\exceptions\NoOriginException;

class SelectiveQuery extends ConditionedQuery {

    protected $selectedColumns = [];
    protected $originPath = '';

    function __construct($object = null) {
        if($object === null){
            return;
        }
        if($object instanceof Select){
            $this->tables = $object->tables;
            $this->selectedColumns = $object->selectedColumns;
        }
        if($object instanceof Show){
            $this->tables = $object->tables;
            $this->selectedColumns = $object->selectedColumns;
        }
        $this->extendOriginPath($object);
    }

    private function extendOriginPath($object){

        try {
            $this->originPath .= ((strlen($this->originPath) != 0) ? '.' : '') . (new ReflectionClass($object))->getShortName();
        } catch (\ReflectionException $e) {
            echo("Reflection class error: ".$e->getMessage());
            exit();
        };
    }

    protected function originatedFrom($path){
        return $this->originPath == $path;
    }


    /**
     * @return string
     * @throws NoOriginException
     */
    function __toString() {
        if($this->originatedFrom('Select')){
            $string = 'SELECT ';
            $string .= $this->arrayToList($this->selectedColumns);
            $string .= 'FROM ';
            $string .= $this->arrayToList($this->tables);
            if(!$this->isConditionEmpty()){
                $string .= $this->createConditionString();
            }
            $string .= $this->getLimitSql().$this->getOffsetSql();
            return $string;
        }
        if($this->originatedFrom('Show')){
            $string = 'SHOW ';
            $string .= $this->arrayToList($this->selectedColumns);
            $string .= 'FROM ';
            $string .= $this->arrayToList($this->tables);
            if(!$this->isConditionEmpty()){
                $string .= $this->createConditionString();
            }
            $string .= $this->getLimitSql().$this->getOffsetSql();
            return $string;
        }
        throw new NoOriginException($this->originPath);

    }

    public function parse(){
        try {
            return $this->__toString();
        } catch (NoOriginException $e) {
            echo("NO ORIGIN IN SELECTIVE QUERY PARSE");
            echo("<pre>");
            echo($e->getMessage());
            echo("</pre>");
            exit();
        }
    }


}