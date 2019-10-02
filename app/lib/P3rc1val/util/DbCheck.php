<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.04.2019
 * Time: 21:36
 */

namespace P3rc1val\util;

use DbMainContext\DbConfig;
use DbMainContext\DbContext;
use P3rc1val\database\structure\TableCreator;
use P3rc1val\Deployment;
use sqlCreator\AlterTable;
use sqlCreator\Create;
use sqlCreator\databaseElement\Column;
use sqlCreator\Insert;
use sqlCreator\Select;
use sqlCreator\Sql;

class DbCheck {

    const LEVEL_OK = "OK";
    const LEVEL_INFO = "INFO";
    const LEVEL_WARN = "WARN";
    const LEVEL_ERR = 'ERR';
    const LEVEL_FATAL = 'FATAL';
    const COLORS = [
        self::LEVEL_OK => 'green',
        self::LEVEL_INFO => 'gray',
        self::LEVEL_ERR => 'yellow',
        self::LEVEL_FATAL => 'red',
        self::LEVEL_WARN => 'orange'
    ];

    /* @var DomBuilder $dom*/
    public static $dom;
    public static $success = true;
    public static $issues = 0;
    public static $fixed = 0;
    public static $warnings = 0;

    private static function log($level, $info){
        if($level == self::LEVEL_FATAL){
            self::$success = false;
        }
        if($level == self::LEVEL_ERR){
            self::$issues++;
        }
        if($level == self::LEVEL_WARN){
            self::$warnings++;
        }
        $element = '<span style="background-color: '.self::COLORS[$level].';">['.$level.']</span>';
        self::$dom->body('[LOG] '.$element.' '.$info.'<br/>');
    }

    private static function getFilesInDir($directory, $includeDirs = false){
        $files = scandir($directory);
        $lim = sizeof($files); //more effective than check every iteration in loop
        for($i = 0; $i < $lim; $i++){
            $fullPath = $directory.DIRECTORY_SEPARATOR.$files[$i];
            $isDir = is_dir($fullPath);
            if($files[$i] == '.' or $files[$i] == '..' or ($isDir and !$includeDirs)){
                array_splice($files, $i, 1);
                $i--;
                $lim--;
                continue;
            }
            $pi = pathinfo($fullPath);
            $files[$i] = [
                'basename' => $files[$i],
                'extension' => $pi['extension'],
                'filename' => $pi['filename'],
                'dir' => $isDir
            ];
        }
        return $files;
    }

    private static function getStructure(){
        $path = __DIR__.DIRECTORY_SEPARATOR.'../database/structure';
        $files = self::getFilesInDir($path);
        $result = [];
        foreach ($files as $file) {
            if($file['filename'] == 'TableCreator'){continue;}
            $cName = 'P3rc1val\\database\\structure\\'.$file['filename'];
            /* @var TableCreator $instance*/
            $instance = new $cName();
            if($instance->getTableName() != null){
                $tableName = $instance->getTableName();
            }else{
                $tableName = strtolower(preg_replace('/([A-Z]+)/', "_$1", lcfirst($file['filename'])));
            }
            $result[$tableName] = [
                'columns' => $instance->getTableStructure()
            ];
            if($instance->getTableValues() != null){
                $result[$tableName]['values'] = $instance->getTableValues();
            }
        }

        return $result;
    }

    private static function importDefaults($name, $values, $columns){
        $data = [$columns,$values];
        $sql = Sql::insert($data, Insert::MODE_BY_ROW)->into($name);
        $result = DbContext::getContext()->query($sql->parse());
        if($result){
            self::log(self::LEVEL_OK, "[OK] Default data inserted successfully");
        }else{
            self::log(self::LEVEL_FATAL, "Error while inserting default values");
            self::log(self::LEVEL_FATAL, "[MYSQL] ".htmlspecialchars($sql->parse()));
            self::log(self::LEVEL_INFO, "[SQL CODE] ".DbContext::getContext()->error);

        }
    }

    private static function alter($name, $values){
        $i = 0;
        $sql = 'ALTER TABLE '.$name.' ';
        foreach ($values as $value) {
            $sql .= $value;
            if($i < count($values)-1){
                $sql .= ', ';
            }
            $i++;
        }
        $result = DbContext::getContext()->query($sql);
        if($result){
            self::log(self::LEVEL_OK, ' No errors while applying alters for table: "'.$name.'"');
        }else{
            self::log(self::LEVEL_FATAL, 'Errors occured while applying alters for table: "'.$name.'"');
            self::log(self::LEVEL_FATAL, '[MYSQL] '.$sql);
            self::log(self::LEVEL_INFO, '[SQL CODE] '.DbContext::getContext()->error);
        }
    }

    private static function createTable($name, $data){
        $sql = Create::table($name);
        foreach ($data['columns'] as $cname => $cdata) {
            $column = new Column($cname);
            $column->setType($cdata['type']);
            if(array_key_exists('key', $cdata)){$column->isKey($cdata['key']);}
            if(array_key_exists('ai', $cdata)){$column->autoIncrement($cdata['ai']);}
            if(array_key_exists('null', $cdata)){$column->nullable($cdata['null']);}
            if(array_key_exists('default', $cdata)){$column->defaultValue($cdata['default']);}
            $sql->column($column);
        }
        $result = DbContext::getContext()->query($sql->parse());
        if($result){
            self::log(self::LEVEL_OK, 'Created table "'.$name.'" with no errors');
            self::$fixed++;
            if(array_key_exists('AlterTable', $data)){
                self::log(self::LEVEL_INFO, 'Found alter queries for table: "'.$name.'" -> applying');
                self::alter($name, $data['AlterTable']);
                self::log(self::LEVEL_INFO, 'Finalized applying alters for table: "'.$name.'"');
            }
            if(array_key_exists('values', $data) and count($data['values']) > 0){
                $columnNames = [];
                foreach ($data['columns'] as $col => $v) {
                    $columnNames[] = $col;
                }
                self::log(self::LEVEL_INFO, 'Found default values for table: "'.$name.'" -> inserting');
                self::importDefaults($name, $data['values'], $columnNames);
                self::log(self::LEVEL_INFO, 'Finalized insertion for table: "'.$name.'"');
            }
        }else{
            self::log(self::LEVEL_FATAL, 'Error while creating table "'.$name.'":');
            self::log(self::LEVEL_FATAL, '[MYSQL] '.DbContext::getContext()->error);
            self::log(self::LEVEL_INFO, '[MYSQL CODE] '.$sql->parse());
        }
    }

    private static function tableExists($name){
        $query = (new Select('1'))->from($name)->limit(1);
        $result = DbContext::getContext()->query($query->parse());
        return ($result !== null && $result !== false);
    }

    private static function checkMinimalValueCount($name, $data){
        $requiredCount = count($data['values']);
        $sql = (new Select('*'))->from($name);
        $result = DbContext::getContext()->query($sql->parse());
        if($result->num_rows >= $requiredCount){
            self::log(self::LEVEL_OK, "Found ".$result->num_rows.' rows, required: '.$requiredCount);
        }else{
            self::log(self::LEVEL_ERR, "Found ".$result->num_rows.' rows, required: '.$requiredCount);
            self::log(self::LEVEL_INFO, $name."->insert_default_values");

            $columnNames = [];
            foreach ($data['columns'] as $col => $v) {
                $columnNames[] = $col;
            }
            self::importDefaults($name, $data['values'], $columnNames);

            self::log(self::LEVEL_INFO, $name."->insert_default_values complete");
        }


    }

    private static function tableStructureCheck($name, $data){
        $query = (new Select('COLUMN_NAME'))
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where([
                'TABLE_SCHEMA' => Deployment::DATABASE['database'],
                'TABLE_NAME' => $name
            ]);
        $result = DbContext::getContext()->query($query->parse());
        $result = DbContext::fetchAll($result, MYSQLI_ASSOC);
        $missing = $data['columns'];

        foreach ($result as $d) {
            $columnName = $d['COLUMN_NAME'];
            if(array_key_exists($columnName, $missing)){
                unset($missing[$columnName]);
            }
        }
        self::log(self::LEVEL_INFO, "Found ".count($result)." columns in table ".$name);
        if(count($missing) > 0){
            self::log(self::LEVEL_ERR, "Table ".$name." incomplete, missing ".count($missing)." columns. Generating...");
            $alter = new AlterTable($name);
            foreach ($missing as $column => $cd) {
                $column = new Column($column);
                $column->setType($cd['type']);
                if(array_key_exists('key', $cd)){$column->isKey($cd['key']);}
                if(array_key_exists('ai', $cd)){$column->autoIncrement($cd['ai']);}
                if(array_key_exists('null', $cd)){$column->nullable($cd['null']);}
                if(array_key_exists('default', $cd)){$column->defaultValue($cd['default']);}
                $alter->add($column);
            }
            if(DbContext::getContext()->query($alter->parse())){
                self::log(self::LEVEL_OK, 'Altered table "'.$name.'" with no errors');
                self::$fixed++;
                if(count($result) > 0){
                    self::log(self::LEVEL_WARN, 'Table "'.$name.'" may have incomplete value set for new columns');
                }else{
                    self::log(self::LEVEL_INFO, 'Table "'.$name.'" was empty, inserting default values');

                    $columnNames = [];
                    foreach ($data['columns'] as $col => $v) {
                        $columnNames[] = $col;
                    }
                    self::importDefaults($name, $data['values'], $columnNames);

                    self::log(self::LEVEL_INFO, 'Table "'.$name.'" default value insertion complete');
                }
            }else{
                self::log(self::LEVEL_FATAL, 'Error while altering table "'.$name.'":');
                self::log(self::LEVEL_FATAL, '[MYSQL] '.DbContext::getContext()->error);
                self::log(self::LEVEL_INFO, '[MYSQL CODE] '.$alter->parse());
            }
        }else{
            self::log(self::LEVEL_OK, "Table ".$name.' is not missing any columns.');
            if(array_key_exists('values', $data)){
                self::log(self::LEVEL_INFO, "Table ".$name."->minimal_value_count_check");
                self::checkMinimalValueCount($name, $data);
                self::log(self::LEVEL_INFO, "Table ".$name."->minimal_value_count_check complete");
            }

        }

    }

    private static function scanDb(){
        $structure = self::getStructure();
        foreach ($structure as $table => $tableData) {
            if(!self::tableExists($table)){
                self::log(self::LEVEL_ERR, "Table not found: ".$table." -> creating");
                self::createTable($table, $tableData);
                self::log(self::LEVEL_INFO, "Table creation finalized: ".$table);
            }else{
                self::log(self::LEVEL_OK, "Table found: ".$table." -> structure checking");
                self::tableStructureCheck($table, $tableData);
                self::log(self::LEVEL_INFO, "Table check finalized: ".$table);
            }
        }
    }

    public static function deploy(){

        self::$dom = new DomBuilder();
        self::log(self::LEVEL_INFO, "Starting new deployment or fix");
        self::scanDb();
        if(!self::$success){
            self::log(self::LEVEL_FATAL, "FATAL ERRORE OCCURED");
        }else{
            self::log(self::LEVEL_INFO, "===========================");
            self::log(self::LEVEL_OK, "DEPLOYMENT SUCCESSFUL");
            self::log(self::LEVEL_OK, "Fixed ".self::$fixed.'/'.self::$issues.' issues');
            if(self::$warnings == 0){
                self::log(self::LEVEL_OK, "No warnings");
            }else{
                self::log(self::LEVEL_WARN, "Warnings occured: ".self::$warnings);
            }

        }
        self::$dom->build(true);
        return self::$success;
    }
}