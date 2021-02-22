<?php

require_once("./src/config/config.php");
require_once("./vendor/autoload.php");
require_once("./src/libs/CurlMulti.php");
require_once("./src/libs/Curl.php");

session_start();

$startTime = microtime(true);

$loop = \React\EventLoop\Factory::create();
$client = new \Clue\React\Buzz\Browser($loop);

$parser = new App\ParserGod($client, $loop);
$parser->run($_REQUEST['start'], $_REQUEST['url']);

$loop->run();

$time = microtime(true) - $startTime;

global $time;

include_once('./src/views/view.php');
