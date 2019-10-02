<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 11.08.2018
 * Time: 00:27
 */

namespace sqlCreator\exceptions;

use Exception;
use Throwable;

class NoOriginException extends Exception {
    function __construct($origin = [], $code = 0, Throwable $previous = null) {
        parent::__construct(print_r($origin, true), $code, $previous);
    }
}