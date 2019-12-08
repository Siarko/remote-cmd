<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 22.12.2018
 * Time: 03:26
 */

class Log {

    private static $logs = [];

    private static $saveCount = 0;

    public static function l($m, $e){
        global $argc, $argv;
        $l = ($e?"ERROR":"INFO");
        self::$logs[] = [
            'l' => $l,
            'm' => $m
        ];
        if($argc == 2){
            //echo("[".$l."] ".$m."\n");
        }
    }

    public static function write($file){
        global $argv, $argc;
        self::$saveCount++;

        $str = '======== LOG '.date("d-m-Y H:i:s")." ========\n";
        $str .= "PID: ".getmypid()."\n";
        $str .= 'CMD: '.print_r($argv, true)."\n";

        foreach (self::$logs as $log) {
            $str .= "[".$log['l']."] ".$log['m']."\n";
        }

        $str .= '======== LOG END ========'."\n";
        if($argv[1] == "stop" || $argc == 2){
            echo($str);
        }else{
            file_put_contents($file, $str, FILE_APPEND);
        }
    }
}

function mylog($mesage, $error = false){
    Log::l($mesage, $error);
}

const __APP_ROOT__ = __DIR__.DIRECTORY_SEPARATOR.'..';

if(PHP_SAPI !== 'cli'){
    echo('CAN RUN ONLY FROM CONSOLE');
    die();
}

chdir(__DIR__);

//disable writing logs to file (log file gets too big)
//TODO add ability to switch logging from server management panel
set_error_handler(function($no, $ms){
    if(strpos($ms, "session_start") !== false){return;}
    chdir(__DIR__);
    mylog("Caught error [".$no."] -> ".$ms);
    //Log::write("WEBSOCKET.log");
});

register_shutdown_function(function(){
    chdir(__DIR__);
    mylog("Saving log cause: shutdown");
    Log::write("WEBSOCKET.log");
});

require_once('vendor/autoload.php');
require_once('../app/lib/DbMainContext/Autoload.php');
require_once('../app/lib/DbModelApi/Autoload.php');
require_once('../app/lib/sqlCreator/Autoload.php');
require_once('../app/lib/P3rc1val/Autoload.php');

use P3rc1val\database\models\Hosts;
use P3rc1val\websocket\ConnectionRegistry;
use P3rc1val\websocket\HostManagement;
use P3rc1val\websocket\RequestHandler;
use Workerman\Worker;

if($argv[1] === "stop"){
    Worker::runAll();
    die();
}


//register direct data passthrough client <=> host
RequestHandler::registerUserPassthrough('console_command');
RequestHandler::registerHostPassthrough('console_output');

RequestHandler::registerUserPassthrough('console_autocomplete');
RequestHandler::registerHostPassthrough('console_autocomplete');


RequestHandler::registerUserHandler('get_host_list', function(){
    $a = Hosts::find()->asArray();
    return $a;
});

RequestHandler::registerUserHandler('authorize_management', function($hostId){
    $result = HostManagement::authorizeManagement($hostId, $_SESSION['login']);
    return ['succeed' => $result];
});

RequestHandler::registerUserHandler('request_stream', function($data){

    ConnectionRegistry::getStaticRegistry($_SESSION['login'])->streamStarted = true;
    ConnectionRegistry::getStaticRegistry($_SESSION['login'])->streamClientNotified = false;

    $hostId = HostManagement::getConnectedHostId($_SESSION['login']);
    //Delete all clips for this host (clear directory)
    $targetDir = \P3rc1val\Config::STREAM_DATA_DIR.$hostId.DIRECTORY_SEPARATOR.'clips';
    $files = glob($targetDir.DIRECTORY_SEPARATOR.'*'); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
    }

    return RequestHandler::notifyHost(
        $hostId,
         'start_stream', [
             'fps' => 10, //frames per second
             'fpc' => intval($data['fpc']), //frames per clip
             'fw' => intval($data['frameWidth']), //frame width
             'fh' => intval($data['frameHeight']), //frame height
             'cmp' => floatval($data['compression']) //frame compression (0.1 - 1)
         ]
    );
});

RequestHandler::registerUserHandler('cancel_stream', function(){

    $hostId = HostManagement::getConnectedHostId($_SESSION['login']);

    return RequestHandler::notifyHost($hostId,'stop_stream', []);
});

RequestHandler::registerUserHandler('disconnect_host', function($hostId){
    $result = HostManagement::disconnectUser($_SESSION['login']);
    return ['succeed' => $result];
});

RequestHandler::registerUserHandler('get_host_uploaded', function(){
    $hostId = HostManagement::getConnectedHostId($_SESSION['login']);

    $dir = \P3rc1val\Config::HOST_UPLOADS_DIR.$hostId;
    $publicDir = \P3rc1val\Config::HOST_UPLOADS_PUBLIC_DIR.$hostId.'/';

    chdir($dir);
    $files = glob("*.zip");
    $result = [];
    foreach ($files as $file) {
        $name = explode('.', $file)[0];
        $info = file_get_contents($name.'_zip.json');
        $result[] = [
            'file' => $file,
            'data' => json_decode($info, true),
            'path' => $publicDir.$file
        ];
    }
    return $result;
});

RequestHandler::registerUserHandler('delete_host_uploaded', function($data){
    $hostId = HostManagement::getConnectedHostId($_SESSION['login']);
    $filename = explode('.', $data['filename'])[0];
    $dir = \P3rc1val\Config::HOST_UPLOADS_DIR.$hostId.DIRECTORY_SEPARATOR;
    if(!unlink($dir.$filename.'.zip')){return false;}
    if(!unlink($dir.$filename.'_zip.json')){return false;}
    return true;
});

/* HOST HANDLERS */

RequestHandler::registerHostHandler('host_ready', function($hostId){
    /* @var $host Hosts*/
    $host = Hosts::find($hostId)->one();
    if($host->user === null){
        HostManagement::disconnectHost($hostId);
        return null;
    }else{
        $host->host_ready = 1;
        $r = RequestHandler::notifyUser($host->user, 'host_ready', $host->hostname);
        if($r){
            return ['succeed' => $host->save()];
        }else{
            return ['succeed' => true];
        }
    }

});

RequestHandler::registerHostHandler('stream_clip', function($hostId, $frameData){

    //create clip from received frames
    $data = \P3rc1val\streaming\Clip::create($hostId, $frameData);

    mylog("[STREAM] New clip created");
    $username = HostManagement::getConnectedUsername($hostId);
    //send notification to user - clip ready to download
    if(!ConnectionRegistry::getStaticRegistry($username)->streamClientNotified){
        //first clip in this stream session
        ConnectionRegistry::getStaticRegistry($username)->streamClientNotified = true;
        RequestHandler::notifyUser($username, "stream_started", $data);
    }else{//new clip available
        RequestHandler::notifyUser($username, "clip_uploaded", $data);
    }

    return null; //no message back to host
});

function execute(){

    $ws_worker = new Worker(\P3rc1val\Deployment::WEBSOCKET_SERVER_URL);
    $ws_worker->count = 1;

    $ws_worker->onConnect = function(\Workerman\Connection\TcpConnection $connection){
        mylog("New connection");
    };

    $ws_worker->onMessage = function(\Workerman\Connection\TcpConnection $connection, $data){
        $data = json_decode($data, true);
        if(!RequestHandler::validateConnection($data)){
            $connection->send(json_encode([
                'type' => 'error',
                'data' => 'connection unathorized'
            ]));
            return;
        }

        if(RequestHandler::isHostRequest($data)){
            $name = $data['hostid'];
            $type = ConnectionRegistry::HOST;
        }else{
            $name = $_SESSION['login'];
            $type = ConnectionRegistry::USER;
        }

        //try to register new connection
        ConnectionRegistry::registerConnection(
            $type,
            $connection->id,
            $name,
            $connection
        );

        //handle request using registered actions
        RequestHandler::handle($connection, $data);
    };

    $ws_worker->onClose = function(\Workerman\Connection\TcpConnection $connection){
        if(ConnectionRegistry::isHost($connection->id)){
            //notify user about host unavailability
            $host = ConnectionRegistry::getHostById($connection->id);
            $user = HostManagement::getConnectedUsername($host);
            RequestHandler::notifyUser($user, 'host_disconnected', []);
            mylog("Host disconnected");
        }else{
            //user disconnected -> disconnect host
            $userName = ConnectionRegistry::getUserById($connection->id);
            HostManagement::disconnectUser($userName);
        }
        //Delete connection data from registry
        ConnectionRegistry::flush($connection->id);
    };

    //reset host states -> reset database fields user,host_ready in Hosts
    HostManagement::resetAllHostStates();
    Worker::runAll();
}

mylog("Starting server");
execute();
mylog("Server normal shutdown");
