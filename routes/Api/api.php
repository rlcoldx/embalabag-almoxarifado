<?php

// API de Produtos
$router->namespace("Agencia\Close\Controllers\Api");
$router->get("/api/produtos/buscar", "ProdutosApiController:buscar");
$router->get("/api/produtos/variacoes/{id}", "ProdutosApiController:variacoes");
$router->get("/api/produtos/sku/{sku}", "ProdutosApiController:porSku");
$router->post("/api/produtos/variacoes/deletar/{id}", "ProdutosApiController:deletarVariacao");

// API de Armazenagens
$router->get("/api/armazenagens/listar", "ArmazenagensApiController:listar");
$router->get("/api/armazenagens/produtos/{id}", "ArmazenagensApiController:getProdutosArmazenados");
$router->get("/api/armazenagens/estatisticas/{id}", "ArmazenagensApiController:getEstatisticas");
$router->get("/api/armazenagens/movimentacoes/{id}", "ArmazenagensApiController:getMovimentacoes");
$router->get("/api/armazenagens/transferencias/{id}", "ArmazenagensApiController:getTransferencias");
$router->get("/api/armazenagens/historico/{id}", "ArmazenagensApiController:getHistorico");
$router->get("/api/armazenagens/estoque/{id}", "ArmazenagensApiController:getEstoque");

// API de Movimentações
$router->post("/api/movimentacoes/criar", "MovimentacoesApiController:criarMovimentacao");
$router->get("/api/movimentacoes/armazenagem/{id}", "MovimentacoesApiController:getMovimentacoesArmazenagem");

// API de Transferências
$router->post("/api/transferencias/criar", "TransferenciasApiController:criar");
$router->get("/api/transferencias/armazenagem/{id}", "TransferenciasApiController:getTransferenciasArmazenagem");

// API de Conferência
$router->get("/api/conferencia/buscar-item", "ConferenciaApiController:buscarItem");
$router->post("/api/conferencia/conferir-item", "ConferenciaApiController:conferirItem");
$router->get("/api/conferencia/relatorio", "ConferenciaApiController:relatorio");
