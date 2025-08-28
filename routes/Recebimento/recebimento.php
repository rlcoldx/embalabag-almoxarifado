<?php
/**
 * Rotas para Recebimento & Armazenagem
 */

// Rotas para Notas Fiscais
$router->namespace("Agencia\Close\Controllers\Recebimento");
$router->get("/recebimento/notas-fiscais", "NotasFiscaisController:index");
$router->get("/recebimento/notas-fiscais/create", "NotasFiscaisController:create");
$router->post("/recebimento/notas-fiscais/store", "NotasFiscaisController:store");
$router->get("/recebimento/notas-fiscais/{id}", "NotasFiscaisController:show");
$router->get("/recebimento/notas-fiscais/{id}/edit", "NotasFiscaisController:edit");
$router->post("/recebimento/notas-fiscais/{id}/update", "NotasFiscaisController:update");
$router->get("/recebimento/notas-fiscais/{id}/delete", "NotasFiscaisController:delete");
$router->get("/recebimento/notas-fiscais/{id}/receber", "NotasFiscaisController:receber");
$router->get("/recebimento/notas-fiscais/{id}/conferir", "NotasFiscaisController:conferir");
$router->post("/recebimento/notas-fiscais/{id}/vincular-pedido", "NotasFiscaisController:vincularPedido");

// Rotas para Conferência (COMENTADAS - Sistema antigo substituído)
// $router->get("/recebimento/conferencia/create", "ConferenciaController:create");
// $router->post("/recebimento/conferencia/store", "ConferenciaController:store");
// $router->get("/recebimento/conferencia/item/{item_nf_id}", "ConferenciaController:conferirItem");
// $router->post("/recebimento/conferencia/item/{item_nf_id}/realizar", "ConferenciaController:realizarConferencia");

// $router->get("/recebimento/conferencia/{id}", "ConferenciaController:show");
// $router->get("/recebimento/conferencia/{id}/edit", "ConferenciaController:edit");
// $router->post("/recebimento/conferencia/{id}/update", "ConferenciaController:update");
// $router->get("/recebimento/conferencia/{id}/delete", "ConferenciaController:delete");

// $router->get("/recebimento/conferencia", "ConferenciaController:index");

// Rotas para Movimentações
$router->get("/recebimento/movimentacoes", "MovimentacoesController:index");
$router->get("/recebimento/movimentacoes/create", "MovimentacoesController:create");
$router->post("/recebimento/movimentacoes/store", "MovimentacoesController:store");
$router->get("/recebimento/movimentacoes/{id}", "MovimentacoesController:show");
$router->get("/recebimento/movimentacoes/{id}/edit", "MovimentacoesController:edit");
$router->post("/recebimento/movimentacoes/{id}/update", "MovimentacoesController:update");
$router->get("/recebimento/movimentacoes/{id}/delete", "MovimentacoesController:delete");
$router->get("/recebimento/movimentacoes/{id}/executar", "MovimentacoesController:executar");
$router->get("/recebimento/movimentacoes/put-away", "MovimentacoesController:putAway");
$router->post("/recebimento/movimentacoes/put-away/store", "MovimentacoesController:realizarPutAway");
$router->get("/recebimento/movimentacoes/transferencia", "MovimentacoesController:transferencia");
$router->post("/recebimento/movimentacoes/transferencia/store", "MovimentacoesController:realizarTransferencia");

// Rotas para Etiquetas
$router->get("/recebimento/etiquetas", "EtiquetasController:index");
$router->get("/recebimento/etiquetas/create", "EtiquetasController:create");
$router->post("/recebimento/etiquetas/store", "EtiquetasController:store");
$router->get("/recebimento/etiquetas/{id}", "EtiquetasController:show");
$router->get("/recebimento/etiquetas/{id}/edit", "EtiquetasController:edit");
$router->post("/recebimento/etiquetas/{id}/update", "EtiquetasController:update");
$router->get("/recebimento/etiquetas/{id}/delete", "EtiquetasController:delete");
$router->get("/recebimento/etiquetas/{id}/imprimir", "EtiquetasController:imprimir");
$router->get("/recebimento/etiquetas/{id}/aplicar", "EtiquetasController:aplicar");
$router->get("/recebimento/etiquetas/gerar/localizacao/{armazenagem_id}", "EtiquetasController:gerarEtiquetaLocalizacao");
$router->get("/recebimento/etiquetas/gerar/produto/{item_nf_id}", "EtiquetasController:gerarEtiquetaProduto");
$router->get("/recebimento/etiquetas/lote/armazenagens", "EtiquetasController:gerarLoteArmazenagens");
$router->get("/recebimento/etiquetas/lote/produtos", "EtiquetasController:gerarLoteProdutos");

// Rotas para Dashboard
$router->get("/recebimento/dashboard", "DashboardController:index");

// Rotas para Relatórios
$router->get("/recebimento/relatorios", "RelatoriosController:index");
$router->get("/recebimento/relatorios/recebimento", "RelatoriosController:recebimento");
$router->get("/recebimento/relatorios/conferencia", "RelatoriosController:conferencia");
$router->get("/recebimento/relatorios/movimentacao", "RelatoriosController:movimentacao");
$router->get("/recebimento/relatorios/etiquetas", "RelatoriosController:etiquetas");