<?php

// Rotas de Armazenagens
$router->namespace("Agencia\Close\Controllers\Armazenagens");

// Rota principal (deve vir antes das rotas com parâmetros)
$router->get("/armazenagens", "ArmazenagensController:index");
// Rotas com parâmetros por último
$router->get("/armazenagens/{id}", "ArmazenagensController:show");
$router->get("/armazenagens/{id}/edit", "ArmazenagensController:edit");
$router->post("/armazenagens/{id}/update", "ArmazenagensController:update");
$router->post("/armazenagens/{id}/delete", "ArmazenagensController:delete");

// Rotas específicas com prioridade máxima
$router->get("/armazenagens/create", "ArmazenagensController:create");
$router->post("/armazenagens/store", "ArmazenagensController:store");
$router->get("/armazenagens/mapa", "ArmazenagensController:mapa");

// Rotas AJAX
$router->get("/armazenagens/buscar-por-codigo", "ArmazenagensController:buscarPorCodigo");
$router->get("/armazenagens/buscar-por-tipo", "ArmazenagensController:buscarPorTipo");
$router->get("/armazenagens/buscar-por-setor", "ArmazenagensController:buscarPorSetor");



// Rotas de Transferências
$router->get("/transferencias", "TransferenciasController:index");
$router->get("/transferencias/create", "TransferenciasController:create");
$router->post("/transferencias/store", "TransferenciasController:store");
$router->post("/transferencias/{id}/execute", "TransferenciasController:execute");
$router->post("/transferencias/{id}/cancel", "TransferenciasController:cancel");
$router->get("/transferencias/{id}/view", "TransferenciasController:view");