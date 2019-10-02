<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.04.2019
 * Time: 23:05
 */

namespace sqlCreator;

class Create {
    public static function table($tableName){
        return new CreateTableQuery($tableName);
    }
}