<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.04.2019
 * Time: 23:15
 */

namespace sqlCreator\databaseElement;


class ColumnType {
    public static function VARCHAR($length){
        return new DataType('varchar', $length);
    }

    public static function INT($length = 11){
        return new DataType('int', $length);
    }

    public static function TEXT(){
        return new DataType('text', null);
    }

    public static function TINYINT(){
        return new DataType('tinyint', 1);
    }

    public static function DATETIME(){
        return new DataType('DATETIME', null);
    }
}