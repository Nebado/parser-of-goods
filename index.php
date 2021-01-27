<?php

require_once("./config.php");
/* require_once("./libs/autoload.php");
 * require_once("./src/ParserGodInterface.php");
 * require_once("./src/ParserGod.php"); */
require_once("./vendor/autoload.php");
require_once("./libs/CurlMulti.php");
require_once("./libs/Curl.php");
require_once("./libs/PhpQuery.php");

session_start();

$startTime = microtime(true);

$parser = new ParserGod\ParserGod();
$parser->process($_POST['start'], $_POST['url']);

global $time;

$time = microtime(true) - $startTime;

include_once('views/view.php');
