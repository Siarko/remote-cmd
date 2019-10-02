<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2018
 * Time: 18:05
 */

namespace sqlCreator;

class Insert extends \sqlCreator\BasicQuery {

    const MODE_BY_ROW = 1;
    const MODE_BY_COLUMN = 2;

    protected $insertedValues = [];
    protected $usedColumns = [];

    /**
     * $mode == MODE_BY_COLUMN
     * $data = [
     *      'kolumna' => ['wartosc1', 'wartosc2'],
     *      'kolumna2' => 'wartosc3'
     * ]
     *
     * $mode == MODE_BY_ROW
     * $data = [
     *      ['kolumna1', 'kolumna2'],
     *      [
     *          ['wartosc1', 'wartosc2'],
     *          ['wartosc1.1', 'wartosc2.1']
     *      ]
     * ]
     *
     * Insert constructor.
     * @param $data array
     * @param $mode Insert
     */
    function __construct($data, $mode) {
        if($mode == self::MODE_BY_COLUMN){
            $maxL = 1;
            foreach ($data as &$value) {//znalezienie najwiekszej liczby wierszy + przeksztalcenie wartosci na tablice
                if(is_array($value)){
                    if(count($value) > $maxL){
                        $maxL = count($value);
                    }
                }else{
                    $value = [$value];
                }
            }

            foreach ($data as $k => $v) { //zapelnienie pustych wierszy
                $count = count($v);
                if($count < $maxL){
                    for($i = 1; $i <= $maxL-$count; $i++){
                        $data[$k][] = 'NULL';
                    }
                }
            }


            foreach ($data as $key => $values) {
                $this->useColumn($key);
                foreach ($values as $rowId => $v){
                    if(!array_key_exists($rowId, $this->insertedValues)){
                        $this->insertedValues[$rowId] = [];
                    }
                    $this->insertedValues[$rowId][] = $v;
                }
            }
        }else{
            $this->parseArgument($data[0], function($column){
                $this->useColumn($column);
            });
            $this->parseArgument($data[1], function($element){
                $this->insertedValues[] = $element;
            });

        }
    }


    private function useColumn($column) {
        if (!in_array($column, $this->usedColumns)) {
            $this->usedColumns[] = $column;
        }
    }

    /**
     * @return string
     */
    private function constructInsertedValues() {
        $sql = '';
        $i = 0;
        foreach ($this->insertedValues as $insertedValue) {
            $sql .= '(';
            $sql .= $this->arrayToList(
                $insertedValue, ', ',
                true, false, '\''
            );
            $sql .= ')';
            if($i < count($this->insertedValues)-1){
                $sql .= ', ';
            }
            $i++;
        }
        return $sql;
    }

    /**
     * @param $tables string
     * @return $this
     */
    public function into($table) {
        if (count($this->tables) == 0) {
            $this->addTable($table);
        }
        return $this;
    }

    public function parse() {
        $sql = "INSERT INTO";
        $sql .= ' '.$this->arrayToList($this->tables);
        $sql .= '(' . $this->arrayToList($this->usedColumns) . ') ';
        $sql .= 'VALUES ';
        $sql .= $this->constructInsertedValues();

        return $sql;
    }
}