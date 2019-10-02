<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 10.08.2018
 * Time: 17:34
 */

namespace sqlCreator;


use DbModelApi\interfaces\IModel;

abstract class BasicQuery {

    /* @var $tables IModel[]*/
    protected $tables = [];

    /**
     * @param $tableName IModel|string
     */
    protected function addTable($tableName){
        if(is_string($tableName)){
            if(!in_array($tableName, $this->tables)){
                $this->tables[] = $tableName;
            }
        }
    }

    /**
     * @param $argument array|string
     * @param $handler callable
     */
    protected function parseArgument($argument, $handler){
        if(is_callable($handler)){
           if(is_array($argument)){
               foreach ($argument as $item) {
                   $handler($item);
               }
           }else{
               $handler($argument);
           }
        }
    }

    /**
     * @param $array array|string
     * @param string $separator
     * @return string
     */
    protected function arrayToList($array, $separator = ', ', $addSpace = true, $onKeys = false, $enclose = ''){

        if(!is_array($array)){
            return $array;
        }
        $string = '';
        $index = 0;
        foreach ($array as $key => $item) {
            $v = (($onKeys)?$key:$item);
            if($v !== null){
                if(gettype($v) === 'string'){
                    $string .= $enclose.$v.$enclose;
                }else{
                    $string .= $v;
                }
            }else{
                $string .= 'NULL';
            }
            if($index < count($array)-1){
                $string .= $separator;
            }
            $index++;
        }
        return $string.(($addSpace)?' ':'');
    }

    /**
     * @return string
     */
    public abstract function parse();
}