<?php

// Rotas para Conferência de Recebimento
$router->namespace("Agencia\Close\Controllers\Conferencia");

// Rotas específicas com prioridade máxima (devem vir ANTES das rotas com parâmetros)
$router->get("/conferencia/create", "ConferenciaRecebimentoController:create");
$router->post("/conferencia/store", "ConferenciaRecebimentoController:store");
$router->get("/conferencia/relatorio", "ConferenciaRecebimentoController:relatorio");
$router->get("/conferencia/nfe/iniciar/{nfe_id}", "ConferenciaRecebimentoController:iniciarConferencia");

// Rota principal
$router->get("/conferencia", "ConferenciaRecebimentoController:index");

// Rotas com parâmetros por último (devem vir DEPOIS das rotas específicas)
//$router->get("/conferencia/show/{id}", "ConferenciaRecebimentoController:show");
$router->get("/conferencia/edit/{id}", "ConferenciaRecebimentoController:edit");
$router->post("/conferencia/update/{id}", "ConferenciaRecebimentoController:update");
$router->post("/conferencia/destroy/{id}", "ConferenciaRecebimentoController:destroy");

