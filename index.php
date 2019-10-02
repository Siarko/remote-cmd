<?php

session_start();

use P3rc1val\auth\User;
use P3rc1val\Config;
use P3rc1val\database\models\Hosts;
use P3rc1val\routing\Router;
use P3rc1val\TemplateParser;
use P3rc1val\Url;
use P3rc1val\util\ArrayUtils;


//const AUTOLOAD_DEBUG = true;

const __APP_ROOT__ = __DIR__;
require_once('app/lib/DbMainContext/Autoload.php');
require_once('app/lib/DbModelApi/Autoload.php');
require_once('app/lib/sqlCreator/Autoload.php');
require_once('app/lib/P3rc1val/Autoload.php');


$htmlTemplate = new TemplateParser();
$htmlTemplate->setFilePath(Config::HTML_TEMPLATES.'MainTemplate.php');
$pageTemplate = new TemplateParser();
$pageTemplate->setFilePath(Config::HTML_TEMPLATES.'PageNotFound.php');

$user = new User();
$user->login();

$router = new Router();

$router->get('^login$', function() use ( $user, $htmlTemplate, $pageTemplate){
    if($user->isLogged()){
        return [Router::REDIRECT => 'home'];
    }
    $htmlTemplate->addCssFile('assets/css/login');
    $pageTemplate->setFilePath(Config::HTML_TEMPLATES.'LoginPanel.php');
});

$router->post('^login$', function() use ($user){
    if(ArrayUtils::requireKeys($_POST, 'login', 'password')){
        $user->login($_POST['login'], $_POST['password']);
        if($user->isLogged()){
            return [Router::REDIRECT => 'home'];
        }else{
            Router::resolver(Router::REDIRECT)
                ->resolve('login')
                ->resolve(['cause' => User::LOGIN_ERR_INVALID_DATA])->end();
        }
    }
    Router::resolver(Router::REDIRECT)
        ->resolve('login')
        ->resolve(['cause' => User::LOGIN_ERR_NO_DATA])->end();
});

$router->get('^logout$', function() use ($user){
    $user->logout();
    return [Router::REDIRECT => 'login'];
});

$router->get('^home$', function() use ($pageTemplate, $user){
    $user->authenticate();
    $pageTemplate->setFilePath(Config::HTML_TEMPLATES.'MainPanel.php');
    $pageTemplate->user = $user;
});

$router->get('^hostapi/isrunning$', function() {

    $r = Router::resolver(Router::JSON);

    if(ArrayUtils::requireKeys($_GET, 'id')) {
        $id = $_GET['id'];
        /* @var $dbRow Hosts | null*/
        $dbRow = Hosts::find($id)->one();
        if($dbRow == null){
            $r->resolve('result.running', false)->end();
        }else{
            $isRunning = (time()-strtotime($dbRow->last_check) <= 6);
            $r->resolve('result.running', $isRunning)->end();
        }
    }else{
        $r->resolve('result.error', 'NO ID')->end();
    }

});

$router->get('^hostapi/queryactivity$', function() {
    $r = Router::resolver(Router::JSON);
    if(ArrayUtils::requireKeys($_GET, 'hostname', 'id')){
        $hostname = $_GET['hostname'];
        $id = $_GET['id'];

        /* @var $dbRow Hosts | null*/
        $dbRow = Hosts::find($id)->one();
        if($dbRow == null){
            $newRow = new Hosts();
            $newRow->id = $id;
            $newRow->hostname = $hostname;
            $newRow->last_check = date('Y-m-d H:i:s',time());
            $newRow->save();

            $r->resolve('result.body', false);
            $r->resolve('result.debug', $newRow->getLastSaveErrors())->end();
        }else{
            $dbRow->last_check = date('Y-m-d H:i:s', time());
            $dbRow->save();
            $r->resolve('result.body', ($dbRow->user != null))->end();
        }
    }else{
        $r->resolve('result.error', 'NO HOSTNAME OR MAC')->end();
    }
});

$router->post('^hostapi/upload$', function(){
    $r = Router::resolver(Router::RAW);
    if(!array_key_exists('ID', $_POST)){
        $r->resolve("NO HOST ID FOUND")->end();
    }
    if(count($_FILES) == 0){
        $r->resolve("NO FILES UPLOADED")->end();
    }
    $hostId = $_POST['ID'];
    $host = Hosts::find($hostId)->one();
    if(!$host){
        $r->resolve("HOST NOT FOUND")->end();
    }
    $hostDir = Config::HOST_UPLOADS_DIR.$hostId;
    if(!file_exists($hostDir)){mkdir($hostDir);}

    foreach ($_FILES as $id => $fileData) {
        $targetName = $hostDir.DIRECTORY_SEPARATOR.$fileData['name'];
        if(!move_uploaded_file($fileData['tmp_name'], $targetName)){
            $r->resolve("CANNOT SAVE UPLOADED FILE")->end();
        }
        $description = $_POST[$id.'_DESC'];
        file_put_contents($hostDir.DIRECTORY_SEPARATOR.$id.'.json', $description);
    }

    $r->resolve("UPLOAD OK")->end();
});

$router->get('^stream/clip$', function() use ($user) {
    /* @var $host Hosts*/
    $host = Hosts::find([Hosts::$USER => @$_SESSION['login']])->one();
    if(!$host){ die(); }
    $clipName = glob('streaming/'.$host->id.'/clips/*.mp4');
    if(count($clipName) > 0){
        $clipName = $clipName[0];
    }else{
        echo(-1);
        die();
    }

    \P3rc1val\streaming\Mp4Reader::read($clipName);
    unlink($clipName);
    die();

});

$router->get('^api/wsserver/state$', function() use ($user) {
    $user->authenticate();

    Router::resolver(Router::JSON)
        ->resolve('state', \P3rc1val\websocket\WsProcess::getState())
        ->end();
});

$router->get('^api/wsserver/togglestate$', function() use ($user) {
    $user->authenticate();

    $state = \P3rc1val\websocket\WsProcess::getState();
    if($state === false){
        $result = \P3rc1val\websocket\WsProcess::start();
    }else{
        $result = \P3rc1val\websocket\WsProcess::kill($state);
    }
    return [
        Router::JSON => [$result]
    ];
});

//register default route
$router->get('.*', function() {
    return [Router::REDIRECT => 'home'];
});

//resolve routing data for this url
$router->route(Url::getSuffix(), Url::getMethod());

//apply default actions for JSON, RAW and REDIRECT
$router->applyRoutingResult();

//parse page content and insert into general template
$htmlTemplate->content = $pageTemplate->parse();
//import assets from page
$htmlTemplate->importAssets($pageTemplate);
//parse general template
$result = $htmlTemplate->parse();

//show page content
if($result !== false){
    echo $result;
}else{
    echo("Brak szablonu: ".$htmlTemplate->getFilePath());
}