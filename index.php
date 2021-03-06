<?php

require_once("./vendor/autoload.php");
require_once("./src/config/config.php");
require_once("./src/config/routes.php");

use App\Components\Route;

session_start();

Route::run();
