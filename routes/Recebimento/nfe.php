<?php
// Rotas para Notas Fiscais Eletrônicas
$router->namespace("Agencia\Close\Controllers\Recebimento");

// Rotas específicas com prioridade máxima (devem vir ANTES das rotas com parâmetros)
$router->get("/recebimento/nfe/create", "NotaFiscalEletronicaController:create");
$router->post("/recebimento/nfe/store", "NotaFiscalEletronicaController:store");
$router->get("/recebimento/nfe/buscar-pedido", "NotaFiscalEletronicaController:buscarPedido");

// Rota principal
$router->get("/recebimento/nfe", "NotaFiscalEletronicaController:index");

// Rotas com parâmetros por último (devem vir DEPOIS das rotas específicas)
$router->get("/recebimento/nfe/show/{id}", "NotaFiscalEletronicaController:show");
$router->get("/recebimento/nfe/edit/{id}", "NotaFiscalEletronicaController:edit");
$router->post("/recebimento/nfe/update/{id}", "NotaFiscalEletronicaController:update");
$router->post("/recebimento/nfe/delete/{id}", "NotaFiscalEletronicaController:delete");
