<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2018
 * Time: 01:36
 */

namespace sqlCreator\traits;

trait LimitingQuery {

    protected $limit = null;

    /**
     * @param $limit int
     */
    public function limit($limit){
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return string
     */
    protected function getLimitSql($space = true){
        if($this->limit){
            return (($space)?' ':'').'LIMIT '.$this->limit;
        }
        return '';
    }
}