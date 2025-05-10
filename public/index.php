<?php

define('BASE_PATH', dirname(__DIR__));

require_once(BASE_PATH . '/vendor/autoload.php');

use App\Utils\Cors;
use App\Utils\Request;
use App\Utils\Router;

$request = new Request();

Cors::handle($request->getMethod());

$router = new Router($request);
$router->handle();
