<?php

use CoffeeCode\Router\Router;

$router = new Router(DOMAIN);

require  __DIR__ . '/Home/home.php';
require  __DIR__ . '/Login/login.php';
require  __DIR__ . '/Migration/migration.php';
require  __DIR__ . '/Users/users.php';
require  __DIR__ . '/Produtos/produtos.php';
require  __DIR__ . '/Conferencia/conferencia.php';
require  __DIR__ . '/Armazenagens/armazenagens.php';
require  __DIR__ . '/Recebimento/recebimento.php';
require  __DIR__ . '/Recebimento/nfe.php';
require  __DIR__ . '/Api/api.php';

// Sempre deixar por último
require  __DIR__ . '/DataTable/datatable.php';

$router->dispatch();
if ($router->error()) {
    echo "Página não encontrada.";
}