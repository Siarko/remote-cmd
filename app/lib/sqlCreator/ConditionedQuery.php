<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2018
 * Time: 01:38
 */

namespace sqlCreator;

use sqlCreator\exceptions\BadSelectiveQueryException;
use sqlCreator\traits\LimitingQuery;
use sqlCreator\traits\OffsetingQuery;

abstract class ConditionedQuery extends \sqlCreator\BasicQuery {

    /* @var $condition \sqlCreator\Condition*/
    protected $condition = null;

    use LimitingQuery, OffsetingQuery;

    protected function addCondition($condition){

        if($this->condition == null){
            $this->condition = $condition;
        }else{
            $this->condition->addSibling($condition);
        }
    }

    protected function isConditionEmpty(){
        return ($this->condition == null);
    }
    /**
     * [
     *   'kolumna' => 'wartosc',
     *   Condition::AND_,
     *   ['kolumna2' => 'wartosc2', Condition::EQUAL],
     *   ['kolumna3', Condition::NOT_NULL]
     * ]
     * @param $conditions
     * @return $this
     */
    public function where($conditions){
        if(gettype($conditions) == 'array'){
            if(count($conditions) > 0){//w formie tablicy asocjacyjnej
                try {
                    $conditionObject = Condition::createFromArray($conditions);
                    $this->addCondition($conditionObject);
                } catch (BadSelectiveQueryException $e) {
                    echo("EXCEPTION! ".$e->getMessage());
                    exit();
                }
            }
        }

        if(gettype($conditions) == 'object' and $conditions instanceof Condition){
            //w formie obiektu klasy Condition
            $this->addCondition($conditions);
        }

        return $this;
    }

    protected function createConditionString() {
        if($this->condition != null){
            return $this->condition->parse();
        }
        return ' ';
    }

}