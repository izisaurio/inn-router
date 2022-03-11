<?php

require '../vendor/autoload.php';

use Inn\Router;

$router = new Router();

$router->match(['GET', 'POST'], '/@@', function() {
	echo 'En  todas papu' . PHP_EOL;
});

$router->get('/', function() {
	echo 'hello!';
	exit();
});

$router->get('/admin/@action', function($params) {
	echo 'administradores/' . $params['action'];
});

$router->get('/static/controller/@id', ['controllers\controller', 'users']);

$router->get('/izisaurio', ['controllers\controller', 'action']);

$router->get('/controller/@method', ['controllers\controller', '@method']);