<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.09.2019
 * Time: 20:06
 */

namespace P3rc1val\routing;


use Throwable;

class ResolverEndException extends \Exception {

    private $resolver;

    function __construct(Resolver $resolver) {
        parent::__construct('Routing resolver called');
        $this->resolver = $resolver;
    }

    function get(){
        return $this->resolver->get();
    }

}