<?php
// Rotas para Notas Fiscais Eletrônicas
$router->namespace("Agencia\Close\Controllers\Recebimento");
$router->get("/recebimento/nfe", "NotaFiscalEletronicaController:index");
$router->get("/recebimento/nfe/create", "NotaFiscalEletronicaController:create");
$router->post("/recebimento/nfe/store", "NotaFiscalEletronicaController:store");
$router->get("/recebimento/nfe/{id}", "NotaFiscalEletronicaController:show");
$router->get("/recebimento/nfe/{id}/edit", "NotaFiscalEletronicaController:edit");
$router->post("/recebimento/nfe/{id}/update", "NotaFiscalEletronicaController:update");
$router->post("/recebimento/nfe/{id}/delete", "NotaFiscalEletronicaController:delete");
$router->get("/recebimento/nfe/buscar-pedido", "NotaFiscalEletronicaController:buscarPedido");
