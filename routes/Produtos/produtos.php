<?php

// PAGE PRODUTOS
$router->namespace("Agencia\Close\Controllers\Produtos");

// Rotas específicas com prioridade máxima (devem vir ANTES das rotas com parâmetros)
$router->get("/produtos/new", "ProdutosController:criar");
$router->get("/produtos/categorias", "CategoriasController:index");
$router->get("/produtos/cores", "CorController:index");
$router->get("/produtos/cores/criar", "CorController:criar");
$router->get("/produtos/estoque-baixo", "ProdutosController:estoqueBaixo");
$router->get("/produtos/exportar-estoque-baixo", "ProdutosController:exportarEstoqueBaixo");

// Rota principal
$router->get("/produtos", "ProdutosController:index");

// Rotas com parâmetros por último (devem vir DEPOIS das rotas específicas)
$router->get("/produtos/edit/{id}", "ProdutosController:editar");
$router->get("/produtos/buscar/{id}", "ProdutosController:buscarProduto");
$router->post("/produtos/save_draft", "ProdutosController:save_draft");
$router->post("/produtos/editar/save", "ProdutosController:save_edit");
$router->post("/produtos/delete/{id}", "ProdutosController:excluir_produto");
$router->post("/produtos/entrada-estoque", "ProdutosController:entradaEstoque");

// PAGE CATEGORIAS
$router->namespace("Agencia\Close\Controllers\Produtos");
$router->get("/produtos/categorias/editar/{id}", "CategoriasController:editar");
$router->post("/produtos/categorias/save", "CategoriasController:save");
$router->post("/produtos/categorias/save_edit", "CategoriasController:save_edit");

// PAGE CORES
$router->namespace("Agencia\Close\Controllers\Produtos");
$router->get("/produtos/cores/editar/{id}", "CorController:editar");
$router->post("/produtos/cores/save", "CorController:save");
$router->post("/produtos/cores/save_edit", "CorController:save_edit");
$router->post("/produtos/cores/excluir", "CorController:remove_color");
$router->get("/produtos/cores/buscar", "CorController:buscar");