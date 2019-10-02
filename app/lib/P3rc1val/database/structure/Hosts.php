<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 29.08.2019
 * Time: 02:25
 */

namespace P3rc1val\database\structure;


class Hosts extends TableCreator {

    /**
     * @return array
     */
    public function getTableStructure() {
        return [
            'id' => [
                'key' => 'PRIMARY',
                'ai' => false,
                'type' => 'varchar(50)',
                'null' => false
            ],
            'hostname' => [
                'type' => 'varchar(50)',
                'null' => false
            ],
            'custom_name' => [
                'type' => 'varchar(30)',
                'null' => true
            ],
            'register_date' => [
                'type' => 'timestamp',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP'
            ],
            'user' => [
                'type' => 'varchar(30)',
                'null' => true
            ],
            'last_check' => [
                'type' => 'timestamp',
                'null' => true
            ],
            'host_ready' => [
                'type' => 'tinyint(1)',
                'null' => false,
                'default' => 0
            ]
        ];
    }
}