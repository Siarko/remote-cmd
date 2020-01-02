<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.09.2019
 * Time: 19:59
 */

namespace P3rc1val\routing;


class Resolver {

    private $type;
    private $output = null;
    private $redirectParams = null;

    function __construct($type) {
        $this->type = $type;
        if($this->type === Router::JSON){
            $this->output = [];
        }

        if($this->type === Router::RAW){
            $this->output = "";
        }
    }

    private function resolveJson($path, $value){
        if(is_array($path)){
            $this->output = $path;
            return;
        }
        $ex = explode('.', $path);
        $current = &$this->output;
        foreach ($ex as $p) {
            /** @noinspection PhpParamsInspection */
            if(!array_key_exists($p, $current)){
                $current[$p] = [];
            }
            $current = &$current[$p];
        }
        $current = $value;
    }

    private function resolveRaw($value){
        $this->output .= $value;
    }

    private function resolveRedirect($value){
        $this->output = $value;
    }

    public function resolve($path, $value = null){
        if($this->type === Router::JSON){
            $this->resolveJson($path, $value);
        }
        if($this->type === Router::RAW){
            $this->resolveRaw($path);
        }
        if($this->type === Router::REDIRECT){
            if(is_array($path)){
                if($this->redirectParams === null){ $this->redirectParams = []; }
                $this->redirectParams = array_merge($this->redirectParams, $path);
            }else{
                $this->resolveRedirect($path);
            }
        }

        return $this;
    }

    /**
     * @throws ResolverEndException
     */
    public function end(){
        throw new ResolverEndException($this);
    }

    function get(){
        $r = [
            $this->type => $this->output
        ];
        if($this->redirectParams !== null){
            $r[Router::PARAMS] = $this->redirectParams;
        }
        return $r;
    }

}