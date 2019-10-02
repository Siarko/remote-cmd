<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 29.08.2019
 * Time: 09:37
 */

namespace P3rc1val;

class Deployment {

    const WEBSOCKET_CLIENT_URL = 'ws://mk.pl:1025';
    const WEBSOCKET_SERVER_URL = 'websocket://127.0.0.1:1025';
    const UDP_SERVER_URL = 'udp://127.0.0.1:1025';

    const DATABASE = [
        'database' => 'remote_cmd',
        'host' => 'localhost',
        'user' => 'h2s',
        'password' => 'h2s'
    ];

}