<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 30.04.2019
 * Time: 16:02
 */
namespace sqlCreator;

use sqlCreator\exceptions\BadSelectiveQueryException;

class Condition{

    const OR_ = 'OR';
    const AND_ = 'AND';
    const DEFAULT_CONNECTION_SIGN = Condition::AND_;

    const EQUAL = '=';
    const NOT_EQUAL = '<>';
    const LESS = '<';
    const GREATER = '>';
    const LESS_OR_EQUAL = '<=';
    const GREATER_OR_EQUAL = '>=';
    const LIKE = 'LIKE';
    const IS_NULL = 'IS NULL';
    const NOT_NULL = 'IS NOT NULL';

    const DEFAULT_SIGN = Condition::EQUAL;

    const CONDITION_SIGNS = [
        Condition::EQUAL, Condition::NOT_EQUAL, Condition::LESS,
        Condition::GREATER, Condition::LESS_OR_EQUAL, Condition::GREATER_OR_EQUAL,
        Condition::LIKE, Condition::IS_NULL, Condition::NOT_NULL
    ];

    const CONDITION_CONNECTION_SIGNS = [
        Condition::OR_, Condition::AND_
    ];

    public static function isConditionSign($sign){
        return in_array($sign, Condition::CONDITION_SIGNS);
    }

    public static function isConditionConnectionSign($sign){
        return in_array($sign, Condition::CONDITION_CONNECTION_SIGNS);
    }

    private $sign;
    private $connectionSign = null;
    private $field = null;
    private $value = null;

    /* @var Condition $siblings*/
    private $siblings = [];

    /**
     * @param array $condition
     * @return Condition
     * @throws BadSelectiveQueryException
     */
    public static function createFromArray(array $condition){
        if(count($condition) == 0){return new Condition();}
        /* @var $conditions Condition[]*/
        $conditions = [];
        $index = -1;
        $lastConnectionSign = null;

        foreach ($condition as $key => $value) {
            if(is_numeric($key)){
                if(is_array($value)){
                    $index++;
                    $c = new Condition();
                    if(array_key_exists(1, $value)){
                        $c->setField($value[0])
                            ->setSign($value[1]);
                    }else{
                        $c->setField(key($value))
                            ->setValue(reset($value))
                            ->setSign($value[0]);
                    }
                    if($lastConnectionSign != null){
                        $c->setConnectionSign($lastConnectionSign);
                        $lastConnectionSign = null;
                    }
                    $conditions[] = $c;
                }else{
                    if($index == -1){
                        throw new BadSelectiveQueryException("First expression invalid");
                    }else{
                        $lastConnectionSign = $value;
                    }
                }
            }else{
                $index++;
                $c = (new Condition())
                    ->setField($key)
                    ->setValue($value);
                if($lastConnectionSign != null){
                    $c->setConnectionSign($lastConnectionSign);
                    $lastConnectionSign = null;
                }
                $conditions[] = $c;
            }
        }

        $c = $conditions[0];
        foreach ($conditions as $k => $con) {
            if($k == 0){continue;}
            $c->addSibling($con);
        }
        return $c;
    }

    function __construct($sign = null) {
        $this->sign = $sign;
    }

    public function setField($field){
        $this->field = $field;
        return $this;
    }

    public function getField(){
        return $this->field;
    }
    /**
     * @return null
     */
    public function getSign() {
        return $this->sign;
    }

    /**
     * @param null $sign
     */
    public function setSign($sign) {
        $this->sign = $sign;
        return $this;
    }

    /**
     * @return null
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param null $value
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Condition
     */
    public function getSiblings(){
        return $this->siblings;
    }

    /**
     * @param Condition $sibling
     */
    public function addSibling(Condition $sibling) {
        $this->siblings[] = $sibling;
        return $this;
    }

    /**
     * @return null
     */
    public function getConnectionSign() {
        if($this->connectionSign == null){
            return Condition::DEFAULT_CONNECTION_SIGN;
        }
        return $this->connectionSign;
    }

    /**
     * @param null $connectionSign
     */
    public function setConnectionSign($connectionSign) {
        $this->connectionSign = $connectionSign;
    }


    public function parse($parent = true) {
        if($this->value == null and $this->field == null){
            return '';
        }
        $spacer = ' ';
        $result = ($parent?'WHERE'.$spacer:'');
        $result .= $this->field.$spacer.(($this->sign==null)?Condition::DEFAULT_SIGN:$this->sign);
        if($this->value != null){
            $result .= $spacer."'".$this->value."'".$spacer;
        }else{
            $result .= $spacer;
        }
        /* @var Condition $sibling*/
        foreach ($this->siblings as $sibling) {
            $result .= $sibling->getConnectionSign().$spacer.'('.$spacer;
            $result .= $sibling->parse(false).$spacer.')'.$spacer;
        }
        return $result;
    }

}