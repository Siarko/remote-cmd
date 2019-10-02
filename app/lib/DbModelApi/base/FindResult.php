<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 15.08.2018
 * Time: 14:53
 */

namespace DbModelApi\base;


class FindResult implements \Iterator {

    private $index = 0;
    private $elements;

    function __construct($elements = []) {
        $this->elements = $elements;
    }

    public function asArray(){
        $result = [];

        /* @var ModelClass $element*/
        foreach ($this->elements as $element) {
            $result[] = $element->jsonSerialize();
        }
        return $result;
    }

    /**
     * @return ModelClass|null
     */
    public function one(){
        if(count($this->elements) == 1){
            return $this->elements[0];
        }else{
            return null;
        }
    }

    /**
     * @return ModelClass[]
     */
    public function all(){
        return $this->elements;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current() {
        return $this->elements[$this->index];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() {
        $this->index++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        return isset($this->elements[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        $this->index = 0;
    }
}