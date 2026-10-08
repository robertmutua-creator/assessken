<?php

use AssessKen\Controllers\HomeController;
use AssessKen\Controllers\TestController;
use AssessKen\Models\Router;

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/database', [TestController::class, 'database']);
return $router;
