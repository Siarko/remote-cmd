<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 12.08.2018
 * Time: 00:41
 */

namespace DbModelApi\base;

use DbMainContext\DbContext;
use DbModelApi\exceptions\ColumnsNotSetException;
use DbModelApi\exceptions\ModelNoColumnException;
use DbModelApi\interfaces\IModel;
use DbModelApi\traits\SmartStaticProperties;
use JsonSerializable;
use sqlCreator\Select;
use sqlCreator\Sql;

abstract class ModelClass implements IModel, JsonSerializable {

    const COLUMN_TYPE_NORMAL = 0;
    const COLUMN_TYPE_SYNTHETIC = 1;

    use SmartStaticProperties;

    protected $dbData = [];

    private $isNew = true;

    private $oldPkValue = null;

    private $syntheticFields = [];

    private $lastSaveError = '';

    function __construct() {
        $this->syntheticFields = $this->createGetterList();

        foreach ($this->syntheticFields as $k => $item) {
            $this->dbData[$k] = [
                'resolver' => $item
            ];
        }
    }

    protected function setDbDataColumn($column, $value){

        $value = $this->onValueSet($column, $value);
        $c = strtolower($column);
        if(!array_key_exists($c, $this->dbData)){
            $this->dbData[$c] = [];
        }
        $this->dbData[strtolower($column)]['value'] = $value;
    }

    protected function onValueSet($column, $value){
        return $value;
    }

    protected function onValueGet($column, $found){
        return $found;
    }

    /**
     * @param $column
     * @return mixed
     * @throws ModelNoColumnException
     */
    protected function getDbDataColumn($column){
        $column = strtolower($column);
        if(array_key_exists($column, $this->dbData)){
            $value = $this->dbData[$column];
            if(array_key_exists('resolver',$value)){
                $resolverName = $value['resolver'];
                $v = ((array_key_exists('value', $value))?$value['value']:null);
                return $this->$resolverName($v);
            }else{
                return $value['value'];
            }
        }else{
            throw new ModelNoColumnException();
        }
    }

    private function createGetterList($prefix = 'onGet'){
        $fields = [];
        $prefixLen = strlen($prefix);
        foreach (get_class_methods(static::class) as $item) {
            if(substr($item, 0, $prefixLen) == $prefix){
                $name = substr($item, $prefixLen, strlen($item));
                $fields[strtolower(preg_replace('/(?<!^)([A-Z])/', '_\\1', $name))] = $item;
            }
        }
        return $fields;
    }

    public function getColumnAssoc($onlyReal = false){
        $assoc = [];
        foreach (static::getColumns() as $key => $column) {
            $type = ModelClass::COLUMN_TYPE_NORMAL;
            $columnName = $column;
            if(is_numeric($column)){
                $type = $column;
                $columnName = $key;
            }
            if($onlyReal and $type == ModelClass::COLUMN_TYPE_SYNTHETIC){
                continue;
            }
            try{
                $value = $this->getDbDataColumn($columnName);
                $assoc[$columnName] = $value;
            }catch (\Exception $e){
                continue;
            }
        }
        return $assoc;
    }

    private function updateContents(){
        $pk = static::getPrimaryKey();

        if($this->oldPkValue == null){
            $this->oldPkValue = $this->getDbDataColumn($pk);
        }
        $sql = Sql::update(static::getTableName());
        $sql->set($this->getColumnAssoc(true));
        $sql->where([$pk => $this->oldPkValue]);
        $result = DbContext::getContext()->query($sql->parse());
        return $result;
    }

    private function createNew(){

        $sql = Sql::insert($this->getColumnAssoc(true));
        $sql->into(static::getTableName());
        $result = DbContext::getContext()->query($sql->parse());
        $this->lastSaveError = DbContext::getError();

        return $result;
    }

    public function save(){
        if(count(static::getColumns()) == 0){
            throw new ColumnsNotSetException();
        }
        if(!$this->isNew){
            return $this->updateContents();
        }else{
            return $this->createNew();
        }

    }

    public function delete(){
        if($this->isNew){
           return false;
        }
        $pk = static::getPrimaryKey();
        $sql = Sql::delete()->from(static::getTableName());
        $sql->where([$pk => $this->getDbDataColumn($pk)]);
        $result = DbContext::getContext()->query($sql->parse());
        return $result;
    }

    public static function getPrimaryKey(){
        if(self::getStaticProp('pkColumn')){
            return self::getStaticProp('pkColumn');
        }
        $show = Sql::show('KEYS')->from(static::getTableName())->where(['Key_name' => 'PRIMARY']);
        $result = DbContext::getContext()->query($show->parse());
        if($result){
            self::setStaticProp('pkColumn', $result->fetch_assoc()['Column_name']);
            return self::getStaticProp('pkColumn');
        }else{
            return 'NOT_SET';
        }
    }

    public function getLastSaveErrors(){
        return $this->lastSaveError;
    }

    /**
     * @param \mysqli_result $result
     * @return FindResult
     */
    private static function parseFindResult($result){
        if($result){
            $result = DbContext::fetchAll($result, MYSQLI_ASSOC);
            $objects = [];
            foreach ($result as $row) {
                $object = new static();
                $object->notNew();
                foreach ($row as $column => $value) {
                    $object->setDbDataColumn($column, $value);
                }
                $objects[] = $object;
            }
            return new FindResult($objects);
        }else{
            return new FindResult();
        }
    }

    /**
     * @param array|string $conditions
     * @return FindResult
     */
    public static function find($conditions = []){
        if(!is_array($conditions)){ //porównanie do klucza głównego
            $condition = [static::getPrimaryKey() => $conditions];
        }else{ //złożone warunkowanie
            $condition = $conditions;
        }
        $sql = (new Select('*'))->from(static::getTableName())->where($condition)->parse();
        $result = DbContext::getContext()->query($sql);
        return self::parseFindResult($result);

    }

    /**
     * @return FindResult
     */
    public static function findAll(){
        $sql = (new Select('*'))->from(static::getTableName())->parse();
        $result = DbContext::getContext()->query($sql);
        return self::parseFindResult($result);
    }

    function __set($name, $value) {
        if($name == static::getPrimaryKey() and !$this->isNew){
            $this->oldPkValue = $this->getDbDataColumn($name);
        }
        $this->setDbDataColumn($name, $value);
    }

    function __get($name) {
        $value = $this->getDbDataColumn($name);
        return $this->onValueGet($name, $value);
    }

    private function notNew(){
        $this->isNew = false;
    }

    public function setAttribute($param, $value) {
        $this->setDbDataColumn($param, $value);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        $result = [];
        foreach (static::getColumns() as $column) {
            $result[$column] = $this->__get($column);
        }

        return $result;
    }
}