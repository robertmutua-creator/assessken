<?php

use AssessKen\Controllers\HomeController;
use AssessKen\Controllers\TestController;

$router->get('/test/database', [TestController::class, 'database']);
$router->get('/test/tables', [TestController::class, 'tables']);
$router->get('/test/seedSchool', [TestController::class, 'seedSchool']);
