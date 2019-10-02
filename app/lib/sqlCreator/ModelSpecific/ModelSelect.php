<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 14.08.2018
 * Time: 18:27
 */

namespace sqlCreator\ModelSpecific;

use sqlCreator\Select;

class ModelSelect extends Select {

    public function from($tableNames){
        /* @var $this \sqlCreator\BasicQuery*/
        $this->parseArgument($tableNames, function($tableName){
            $this->addTable($tableName);
        });
        return new ModelSelectiveQuery($this);
    }

}