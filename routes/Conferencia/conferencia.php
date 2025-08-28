<?php

// Rotas para Conferência de Recebimento
$router->namespace("Agencia\Close\Controllers\Conferencia");

// Rota principal (deve vir antes das rotas com parâmetros)
$router->get("/conferencia", "ConferenciaRecebimentoController:index");

// Rotas específicas com prioridade máxima
$router->get("/conferencia/create", "ConferenciaRecebimentoController:create");
$router->post("/conferencia/store", "ConferenciaRecebimentoController:store");
$router->get("/conferencia/relatorio", "ConferenciaRecebimentoController:relatorio");

// Rotas com parâmetros por último
//$router->get("/conferencia/{id}", "ConferenciaRecebimentoController:show");
$router->get("/conferencia/{id}/edit", "ConferenciaRecebimentoController:edit");
$router->post("/conferencia/{id}/update", "ConferenciaRecebimentoController:update");
$router->post("/conferencia/{id}/destroy", "ConferenciaRecebimentoController:destroy");

// Rotas para iniciar conferência de NFE específica
$router->get("/conferencia/nfe/{nfe_id}/iniciar", "ConferenciaRecebimentoController:iniciarConferencia");

