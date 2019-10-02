<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 10.08.2018
 * Time: 17:42
 */

namespace DbModelApi\interfaces;

interface IModel {

    /**
     * @return string
     */
    public static function getTableName();

    /**
     * @return string[]
     */
    public static function getColumns();

}