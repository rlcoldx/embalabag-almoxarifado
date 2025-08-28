<?php
// Rotas para DataTable
$router->namespace("Agencia\Close\Controllers\DataTable");

// Endpoints para buscar dados
$router->get("/api/users", "UsersDataTableController:index");
$router->get("/api/cargos", "CargosDataTableController:index");
$router->get("/permissoes", "PermissoesDataTableController:index");
$router->get("/log-acessos", "LogAcessosDataTableController:index");

// Rota para DataTable de produtos
$router->get("/datatable/produtos", "ProdutosDataTableController:index");

// Rota para DataTable de notas fiscais
$router->get("/datatable/notas-fiscais", "NotasFiscaisDataTableController:index");

// Rota para DataTable de armazenagens
$router->get("/api/armazenagens", "ArmazenagensDataTableController:index");

// Rota para DataTable de recebimentos
$router->get("/api/recebimentos", "RecebimentosDataTableController:index");

// Rota para DataTable de NF-e
$router->post("/datatable/nfe", "NfeDataTableController:index");

// Rota para DataTable de produtos (corrigir para usar /api/)
$router->get("/api/produtos", "ProdutosDataTableController:index");

// Rota para DataTable de conferências
$router->get("/api/conferencia/listar", "ConferenciaDataTableController:listar");

// Endpoints para exportação CSV
$router->get("/export/users", "UsersDataTableController:export");
$router->get("/export/cargos", "CargosDataTableController:export");
$router->get("/export/permissoes", "PermissoesDataTableController:export");
$router->get("/export/log-acessos", "LogAcessosDataTableController:export");
