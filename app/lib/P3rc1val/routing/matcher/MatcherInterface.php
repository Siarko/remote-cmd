<?php
namespace P3rc1val\routing\matcher;

interface MatcherInterface {

    public function match(string $url, string $regex) : array;
}