<?php

require_once("./config.php");
// require_once("./libs/autoload.php");
// require_once("./app/ParserGodInterface.php");
// require_once("./app/ParserGod.php");
require_once("./vendor/autoload.php");
require_once("./libs/CurlMulti.php");
require_once("./libs/Curl.php");
require_once("./libs/PhpQuery.php");

session_start();

$startTime = microtime(true);

$loop = \React\EventLoop\Factory::create();
$client = new \Clue\React\Buzz\Browser($loop);

$parser = new App\ParserGod($client, $loop);
$parser->run($_POST['start'], $_POST['url']);

$loop->run();

$time = microtime(true) - $startTime;

global $time;

include_once('views/view.php');
