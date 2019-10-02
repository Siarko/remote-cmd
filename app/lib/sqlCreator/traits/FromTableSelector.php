<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2018
 * Time: 23:23
 */

namespace sqlCreator\traits;

trait FromTableSelector{

    /**
     * @param $tableNames string|string[]
     */
    public function from($tableNames){
        /* @var $this \sqlCreator\BasicQuery*/
        $this->parseArgument($tableNames, function($tableName){
            $this->addTable($tableName);
        });
        return $this;
    }
}