<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 29.08.2019
 * Time: 02:14
 */
session_start();

const __APP_ROOT__ = __DIR__;
require_once('app/lib/DbMainContext/Autoload.php');
require_once('app/lib/DbModelApi/Autoload.php');
require_once('app/lib/sqlCreator/Autoload.php');
require_once('app/lib/P3rc1val/Autoload.php');

\P3rc1val\util\DbCheck::deploy();