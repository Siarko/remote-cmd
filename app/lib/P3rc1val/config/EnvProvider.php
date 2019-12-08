<?php

namespace P3rc1val\config;

class EnvProvider {

    public static function getEnvKey($key, $alternative = null, $path = __APP_ROOT__.'/env.php'){
        $content = [];
        if(file_exists($path)){
            $content = require $path;
        }
        if(is_array($content) and array_key_exists($key, $content)){
            return $content[$key];
        }
        return $alternative;
    }
}