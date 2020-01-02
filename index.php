<?php
session_start();

use frontend\actions\EntryActions;

use frontend\actions\HostApiActions;
use frontend\actions\HostDataActions;
use frontend\actions\JarActions;
use frontend\actions\StreamActions;
use frontend\actions\WsServerActions;
use P3rc1val\auth\User;
use P3rc1val\Config;
use P3rc1val\logger\Logger;
use P3rc1val\routing\Router;
use P3rc1val\templates\TemplateParser;
use P3rc1val\Url;


//const AUTOLOAD_DEBUG = true;

const __APP_ROOT__ = __DIR__;
require_once('app/frontend/Autoload.php');
require_once('app/lib/DbMainContext/Autoload.php');
require_once('app/lib/DbModelApi/Autoload.php');
require_once('app/lib/sqlCreator/Autoload.php');
require_once('app/lib/P3rc1val/Autoload.php');

Logger::install();


$htmlTemplate = new TemplateParser();
$htmlTemplate->setFilePath(Config::HTML_TEMPLATES.'MainTemplate.php');
$pageTemplate = new TemplateParser();
$pageTemplate->setFilePath(Config::HTML_TEMPLATES.'BlankPage.php');

$user = new User();
$user->login();

$router = new Router();

$router->get('^login$', EntryActions::login($user, $htmlTemplate, $pageTemplate));
$router->post('^login$', EntryActions::loginPost($user));

$router->get('^logout$', EntryActions::logout($user));

$router->get('^home$', EntryActions::home($pageTemplate, $user));
$router->get('^$', EntryActions::home($pageTemplate, $user));

$router->get('^getjar$', JarActions::get());

$router->get('^hostapi/isrunning$', HostApiActions::isRunning());
$router->get('^hostapi/queryactivity$', HostApiActions::queryActivity());
$router->post('^hostapi/upload$', HostApiActions::upload());

$router->get('^stream/clip$', StreamActions::clip($user));

$router->get('^api/wsserver/state$', WsServerActions::getState($user));
$router->get('^api/wsserver/togglestate$', WsServerActions::toggleState($user));
$router->post('^api/host/$hostId$', HostDataActions::setCustomName($user));


$router->get('.*', function() use ($pageTemplate){
    $pageTemplate->setFilePath(Config::HTML_TEMPLATES.'PageNotFound.php');
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