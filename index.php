<?php
require_once("./config.php");
/* require_once("./libs/autoload.php"); */
require_once("./vendor/autoload.php");
require_once("./libs/CurlMulti.php");
require_once("./libs/Curl.php");
require_once("./libs/PhpQuery.php");
require_once('./App/ParserGod.php');

session_start();

$startTime = microtime(true);

$parser = new App\ParserGod();
$parser->process($_POST['start'], $_POST['url']);

global $time;

$time = microtime(true) - $startTime;

include_once('views/view.php');
