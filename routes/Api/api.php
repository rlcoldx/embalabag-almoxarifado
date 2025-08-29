<?php

// API de Produtos
$router->namespace("Agencia\Close\Controllers\Api");
$router->get("/api/produtos/buscar", "ProdutosApiController:buscar");
$router->get("/api/produtos/variacoes/{id}", "ProdutosApiController:variacoes");
$router->get("/api/produtos/sku/{sku}", "ProdutosApiController:porSku");

// API de Armazenagens
$router->get("/api/armazenagens", "ArmazenagensApiController:listarArmazenagens");
$router->get("/api/armazenagens/produtos/{id}", "ArmazenagensApiController:getProdutosArmazenados");
$router->get("/api/armazenagens/estatisticas/{id}", "ArmazenagensApiController:getEstatisticas");
$router->get("/api/armazenagens/movimentacoes/{id}", "ArmazenagensApiController:getMovimentacoes");
$router->get("/api/armazenagens/transferencias/{id}", "ArmazenagensApiController:getTransferencias");
$router->get("/api/armazenagens/historico/{id}", "ArmazenagensApiController:getHistorico");

// API de Movimentações
$router->post("/api/movimentacoes/criar", "MovimentacoesApiController:criarMovimentacao");
$router->get("/api/movimentacoes/armazenagem/{id}", "MovimentacoesApiController:getMovimentacoesArmazenagem");

// API de Transferências
$router->get("/api/transferencias/criar", "TransferenciasApiController:criarTransferencia");
$router->get("/api/transferencias/armazenagem/{id}", "TransferenciasApiController:getTransferenciasArmazenagem");
