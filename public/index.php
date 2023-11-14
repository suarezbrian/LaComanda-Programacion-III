<?php


use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/db.php';

$app = AppFactory::create();

$app->getContainer()['EmpleadoController'] = function ($container) {
    return new EmpleadoController($container);
};
$app->getContainer()['MesaController'] = function ($container) {
    return new MesaController($container);
};
$app->getContainer()['ProductoController'] = function ($container) {
    return new ProductoController($container);
};
$app->getContainer()['PedidoController'] = function ($container) {
    return new PedidoController($container);
};
$app->getContainer()['UsuarioController'] = function ($container) {
    return new UsuarioController($container);
};

$routes = require __DIR__ . '/../src/routes/routes.php';
$routes($app);

$app->run();
