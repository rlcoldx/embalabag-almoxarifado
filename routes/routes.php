<?php

use CoffeeCode\Router\Router;

$router = new Router(DOMAIN);

// PAGE HOME
$router->namespace("Agencia\Close\Controllers\Home");
$router->get("/", "HomeController:index");


$router->dispatch();
if ($router->error()) {
    echo "Página não encontrada.";
}