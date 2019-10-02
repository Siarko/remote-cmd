<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2019
 * Time: 20:50
 */

namespace P3rc1val\database\models;

use DbModelApi\base\ModelClass;

/**
 * @property string $id
 * @property string $hostname
 * @property string $custom_name
 * @property int $register_date
 * @property string $user
 * @property int $last_check
 * @property int $host_ready
 * */
class Hosts extends ModelClass {

    public static $ID = 'id';
    public static $HOSTNAME = 'hostname';
    public static $CUSTOM_NAME = 'custom_name';
    public static $REGISTER_DATE = 'register_date';
    public static $USER = 'user';
    public static $LAST_CHECK = 'last_check';
    public static $HOST_READY = 'host_ready';

    public static function getTableName() {
        return 'hosts';
    }

    /**
     * @return string[]
     */
    public static function getColumns() {
        return [
            self::$ID,
            self::$HOSTNAME,
            self::$CUSTOM_NAME,
            self::$REGISTER_DATE,
            self::$USER,
            self::$LAST_CHECK,
            self::$HOST_READY
        ];
    }
}