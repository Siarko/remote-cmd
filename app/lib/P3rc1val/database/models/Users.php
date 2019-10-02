<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2019
 * Time: 10:48
 */

namespace P3rc1val\database\models;

use DbModelApi\base\ModelClass;

/**
 * @property string $login
 * @property string $password
 * @property string $nick
 * */
class Users extends ModelClass {

    public static $LOGIN = 'login';
    public static $PASSWORD = 'password';
    public static $NICK = 'nick';

    public static function getTableName() {
        return 'users';
    }

    /**
     * @return string[]
     */
    public static function getColumns() {
        return [
            self::$LOGIN,
            self::$PASSWORD,
            self::$NICK
        ];
    }
}