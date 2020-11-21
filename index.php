<?php

require_once('./vendor/autoload.php');
require_once("./libs/CurlMulti.php");
require_once("./libs/Curl.php");

session_start();

$startTime = microtime(true);

include_once('app/Page.php');

global $time;
$time = microtime(true) - $startTime;

include_once('views/index.php');

