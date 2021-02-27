<?php

require_once("./src/config/config.php");
require_once("./vendor/autoload.php");

session_start();

$startTime = microtime(true);

$loop = \React\EventLoop\Factory::create();
$client = new \Clue\React\Buzz\Browser($loop);

$parser = new App\ParserGod($client, $loop);
$parser->init();
$parser->run($_REQUEST['start'], $_REQUEST['url']);

$loop->run();

$time = microtime(true) - $startTime;
global $time;
