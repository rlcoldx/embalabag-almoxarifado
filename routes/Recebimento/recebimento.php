<?php
/**
 * Rotas para Recebimento & Armazenagem
 */

// Rotas para Notas Fiscais
$router->namespace("Agencia\Close\Controllers\Recebimento");

// Rotas específicas com prioridade máxima (devem vir ANTES das rotas com parâmetros)
$router->get("/recebimento/notas-fiscais/create", "NotasFiscaisController:create");
$router->post("/recebimento/notas-fiscais/store", "NotasFiscaisController:store");
$router->get("/recebimento/movimentacoes/create", "MovimentacoesController:create");
$router->post("/recebimento/movimentacoes/store", "MovimentacoesController:store");
$router->get("/recebimento/movimentacoes/put-away", "MovimentacoesController:putAway");
$router->post("/recebimento/movimentacoes/put-away/store", "MovimentacoesController:realizarPutAway");
$router->get("/recebimento/movimentacoes/transferencia", "MovimentacoesController:transferencia");
$router->post("/recebimento/movimentacoes/transferencia/store", "MovimentacoesController:realizarTransferencia");
$router->get("/recebimento/etiquetas/create", "EtiquetasController:create");
$router->post("/recebimento/etiquetas/store", "EtiquetasController:store");
$router->get("/recebimento/dashboard", "DashboardController:index");
$router->get("/recebimento/relatorios", "RelatoriosController:index");
$router->get("/recebimento/relatorios/recebimento", "RelatoriosController:recebimento");
$router->get("/recebimento/relatorios/conferencia", "RelatoriosController:conferencia");
$router->get("/recebimento/relatorios/movimentacao", "RelatoriosController:movimentacao");
$router->get("/recebimento/relatorios/etiquetas", "RelatoriosController:etiquetas");

// Rotas com parâmetros por último (devem vir DEPOIS das rotas específicas)
$router->get("/recebimento/notas-fiscais/show/{id}", "NotasFiscaisController:show");
$router->get("/recebimento/notas-fiscais/edit/{id}", "NotasFiscaisController:edit");
$router->post("/recebimento/notas-fiscais/update/{id}", "NotasFiscaisController:update");
$router->get("/recebimento/notas-fiscais/delete/{id}", "NotasFiscaisController:delete");
$router->get("/recebimento/notas-fiscais/receber/{id}", "NotasFiscaisController:receber");
$router->get("/recebimento/notas-fiscais/conferir/{id}", "NotasFiscaisController:conferir");
$router->post("/recebimento/notas-fiscais/vincular-pedido/{id}", "NotasFiscaisController:vincularPedido");

// Rotas para Movimentações
$router->get("/recebimento/movimentacoes/show/{id}", "MovimentacoesController:show");
$router->get("/recebimento/movimentacoes/edit/{id}", "MovimentacoesController:edit");
$router->post("/recebimento/movimentacoes/update/{id}", "MovimentacoesController:update");
$router->get("/recebimento/movimentacoes/delete/{id}", "MovimentacoesController:delete");
$router->get("/recebimento/movimentacoes/executar/{id}", "MovimentacoesController:executar");

// Rotas para Etiquetas
$router->get("/recebimento/etiquetas/show/{id}", "EtiquetasController:show");
$router->get("/recebimento/etiquetas/edit/{id}", "EtiquetasController:edit");
$router->post("/recebimento/etiquetas/update/{id}", "EtiquetasController:update");
$router->get("/recebimento/etiquetas/delete/{id}", "EtiquetasController:delete");
$router->get("/recebimento/etiquetas/imprimir/{id}", "EtiquetasController:imprimir");
$router->get("/recebimento/etiquetas/aplicar/{id}", "EtiquetasController:aplicar");
$router->get("/recebimento/etiquetas/gerar/localizacao/{armazenagem_id}", "EtiquetasController:gerarEtiquetaLocalizacao");
$router->get("/recebimento/etiquetas/gerar/produto/{item_nf_id}", "EtiquetasController:gerarEtiquetaProduto");
$router->get("/recebimento/etiquetas/lote/armazenagens", "EtiquetasController:gerarLoteArmazenagens");
$router->get("/recebimento/etiquetas/lote/produtos", "EtiquetasController:gerarLoteProdutos");