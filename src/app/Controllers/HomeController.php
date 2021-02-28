<?php

namespace App\Controllers;

use App\Components\Template;

class HomeController {
    
    public function index()
    {
        $template = new Template();
        $template->render('index.html');
    }
}
