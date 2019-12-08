<?php


namespace P3rc1val\routing\matcher;


class ParametricMatcher extends AbstractMatcher implements MatcherInterface {

    private function transformRouteParams($route){
        return preg_replace('/(\$([a-zA-Z-\_]+))/', '(?<$2>[^/.]*)', $route);
    }

    public function match(string $url, string $regex): array {
        $regex = $this->transformRouteParams($regex);
        $result = [];
        preg_match($regex, $url, $result);
        return $result;
    }
}