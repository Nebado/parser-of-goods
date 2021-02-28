<?php

use App\Components\Route;

require_once("./vendor/autoload.php");
require_once("./src/config/config.php");
require_once("./src/config/routes.php");

session_start();

Route::run();
