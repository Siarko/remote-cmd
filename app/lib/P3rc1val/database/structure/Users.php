<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 29.08.2019
 * Time: 02:43
 */

namespace P3rc1val\database\structure;


class Users extends TableCreator {

    /**
     * @return array
     */
    public function getTableStructure() {
        return [
            'login' => [
                'key' => 'PRIMARY',
                'ai' => false,
                'type' => 'varchar(30)',
                'null' => false
            ],
            'password' => [
                'type' => 'varchar(30)',
                'null' => false
            ],
            'nick' => [
                'type' => 'varchar(30)',
                'null' => true,
            ]
        ];
    }

    public function getTableValues() {
        return [
            ['siarko', 'siarko', 'h2s']
        ];
    }


}