<?php


namespace P3rc1val\routing\matcher;


abstract class AbstractMatcher implements MatcherInterface {

    private function packageRegex($string){
        return '#'.$string.'#';
    }

    public function _match(string $url, string $regex) : array{
        $regex = $this->packageRegex($regex);
        $result = $this->match($url, $regex);
        return $result;
    }

    public abstract function match(string $url, string $regex) : array;
}