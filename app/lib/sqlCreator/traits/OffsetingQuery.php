<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2018
 * Time: 01:41
 */

namespace sqlCreator\traits;

trait OffsetingQuery {

    protected $offset = null;

    /**
     * @param $offset int
     */
    public function offset($offset){
        $this->offset = $offset;
        return $this;
    }

    protected function getOffsetSql($space = true){
        if($this->offset){
            return  (($space)?' ':'').'OFFSET '.$this->offset;
        }
        return '';
    }
}