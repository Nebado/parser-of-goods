<?php

use App\Components\Route;

Route::get('/', 'HomeController@index');
Route::get('/parser', 'ParserGodController@run', 'post');
