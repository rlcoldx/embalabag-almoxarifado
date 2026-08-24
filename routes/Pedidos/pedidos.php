<?php

$router->namespace("Agencia\Close\Controllers\Pedidos");

$router->get("/pedidos/create", "PedidosController:create");
$router->post("/pedidos/store", "PedidosController:store");

$router->get("/pedidos", "PedidosController:index");

$router->get("/pedidos/edit/{id}", "PedidosController:edit");
$router->get("/pedidos/show/{id}", "PedidosController:show");
$router->post("/pedidos/update/{id}", "PedidosController:update");
$router->post("/pedidos/delete/{id}", "PedidosController:delete");
$router->post("/pedidos/aprovar/{id}", "PedidosController:aprovar");
$router->post("/pedidos/cancelar/{id}", "PedidosController:cancelar");
