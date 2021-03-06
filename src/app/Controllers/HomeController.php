<?php

namespace App\Controllers;

use App\Components\Template;
use DebugBar\StandardDebugBar;

class HomeController {

    public function index()
    {
        $debugbar = new StandardDebugBar();
        $debugbarRenderer = $debugbar->getJavascriptRenderer();

        $template = new Template();
        $template->render('index.html', ['debugbarRenderer' => $debugbarRenderer]);
    }
}
