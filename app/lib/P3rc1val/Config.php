<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 22.12.2018
 * Time: 03:20
 */

namespace P3rc1val;

class Config {
    const WS_WORKER_STATE_FILENAME = 'ws_state.info';
    const WS_WORKER_STATE_PATH = __APP_ROOT__ . DIRECTORY_SEPARATOR .
        'servers' .
        DIRECTORY_SEPARATOR .
        self::WS_WORKER_STATE_FILENAME;
    const WS_WORKER_SERVER = __APP_ROOT__.DIRECTORY_SEPARATOR.'servers'.DIRECTORY_SEPARATOR.'ws_server.php';

    const STREAM_DATA_DIR = __APP_ROOT__.DIRECTORY_SEPARATOR.'streaming'.DIRECTORY_SEPARATOR;

    const HTML_TEMPLATES = __APP_ROOT__ . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR;
    const HOST_UPLOADS_DIR = __APP_ROOT__.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'hosts'.DIRECTORY_SEPARATOR;

    const HOST_UPLOADS_PUBLIC_DIR = 'uploads/hosts/';
    const CLIENT_UPLOADS_DIR = __APP_ROOT__.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'clients'.DIRECTORY_SEPARATOR;

    const JAR_DIRECTORY = __APP_ROOT__.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'jar';
    const JAR_DIRECTORY_PUBLIC_DIR = 'uploads/jar/';
}