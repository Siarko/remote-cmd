<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 08.09.2019
 * Time: 13:34
 */

namespace P3rc1val\streaming;


class Mp4Reader {

    public static function read($path){
        if(!file_exists($path)){
            echo("FILE NOT EXISTS!");
            die();
        }

        $size=filesize($path);

        $fm=@fopen($path,'rb');
        if(!$fm) {
            // You can also redirect here
            header ("HTTP/1.0 404 Not Found");
            die();
        }

        $begin=0;
        $end=$size;

        if(isset($_SERVER['HTTP_RANGE'])) {
            if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $begin=intval($matches[0]);
                if(!empty($matches[1])) {
                    $end=intval($matches[1]);
                }
            }
        }

        if($begin>0||$end<$size)
            header('HTTP/1.0 206 Partial Content');
        else
            header('HTTP/1.0 200 OK');

        header("Content-Type: video/mp4");
        header('Accept-Ranges: bytes');
        header('Content-Length:'.($end-$begin));
        header("Content-Disposition: inline;");
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Transfer-Encoding: binary\n");
        header('Connection: close');

        $cur=$begin;
        fseek($fm,$begin,0);

        while(!feof($fm)&&$cur<$end&&(connection_status()==0))
        { print fread($fm,min(1024*16,$end-$cur));
            $cur+=1024*16;
            usleep(1000);
        }
        fclose($fm);
    }
}