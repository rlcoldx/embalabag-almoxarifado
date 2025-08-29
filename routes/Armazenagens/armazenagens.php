<?php

// Rotas de Armazenagens
$router->namespace("Agencia\Close\Controllers\Armazenagens");

// Rotas específicas com prioridade máxima (devem vir ANTES das rotas com parâmetros)
$router->get("/armazenagens/create", "ArmazenagensController:create");
$router->post("/armazenagens/store", "ArmazenagensController:store");
$router->get("/armazenagens/mapa", "ArmazenagensController:mapa");

// Rotas AJAX
$router->get("/armazenagens/buscar-por-codigo", "ArmazenagensController:buscarPorCodigo");
$router->get("/armazenagens/buscar-por-tipo", "ArmazenagensController:buscarPorTipo");
$router->get("/armazenagens/buscar-por-setor", "ArmazenagensController:buscarPorSetor");

// Rota principal
$router->get("/armazenagens", "ArmazenagensController:index");

// Rotas com parâmetros por último (devem vir DEPOIS das rotas específicas)
$router->get("/armazenagens/edit/{id}", "ArmazenagensController:edit");
$router->get("/armazenagens/show/{id}", "ArmazenagensController:show");
$router->post("/armazenagens/update/{id}", "ArmazenagensController:update");
$router->post("/armazenagens/delete/{id}", "ArmazenagensController:delete");

// Rotas de Transferências
$router->get("/transferencias", "TransferenciasController:index");
$router->get("/transferencias/create", "TransferenciasController:create");
$router->post("/transferencias/store", "TransferenciasController:store");
$router->post("/transferencias/execute/{id}", "TransferenciasController:execute");
$router->post("/transferencias/cancel/{id}", "TransferenciasController:cancel");
$router->post("/transferencias/view/{id}", "TransferenciasController:view");