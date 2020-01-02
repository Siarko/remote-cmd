<?php


namespace frontend\actions;


use P3rc1val\Config;
use P3rc1val\routing\Router;
use P3rc1val\Url;

/**
 * @method static get()
 */
class JarActions extends AbstractActions {

    /**
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionGet(){
        $result = Router::resolver(Router::JSON);
        $files = glob(Config::JAR_DIRECTORY.DIRECTORY_SEPARATOR.'*.jar');
        $files = array_reverse($files);
        if(count($files) > 0){
            $filename = basename($files[0]);
            $url = Config::JAR_DIRECTORY_PUBLIC_DIR.$filename;

            $result->resolve('filename', $filename);
            $result->resolve('url', $url)->end();
        }
        $result->resolve('error', 'No jar file found')->end();
    }

}