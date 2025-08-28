<?php

// PAGE PRODUTOS
$router->namespace("Agencia\Close\Controllers\Produtos");
$router->get("/produtos", "ProdutosController:index");
$router->get("/produtos/new", "ProdutosController:criar");
$router->get("/produtos/edit/{id}", "ProdutosController:editar");
$router->post("/produtos/save_draft", "ProdutosController:save_draft");
$router->post("/produtos/editar/save", "ProdutosController:save_edit");
$router->post("/produtos/excluir", "ProdutosController:excluir_produto");

// PAGE CATEGORIAS
$router->namespace("Agencia\Close\Controllers\Produtos");
$router->get("/produtos/categorias", "CategoriasController:index");
$router->get("/produtos/categorias/editar/{id}", "CategoriasController:editar");
$router->post("/produtos/categorias/save", "CategoriasController:save");
$router->post("/produtos/categorias/save_edit", "CategoriasController:save_edit");

// PAGE CORES
$router->namespace("Agencia\Close\Controllers\Produtos");
$router->get("/produtos/cores", "CorController:index");
$router->get("/produtos/cores/criar", "CorController:criar");
$router->get("/produtos/cores/editar/{id}", "CorController:editar");
$router->post("/produtos/cores/save", "CorController:save");
$router->post("/produtos/cores/save_edit", "CorController:save_edit");
$router->post("/produtos/cores/excluir", "CorController:remove_color");
$router->get("/produtos/cores/buscar", "CorController:buscar");

// PAGE ESTOQUE BAIXO
$router->namespace("Agencia\Close\Controllers\Produtos");
$router->get("/produtos/estoque-baixo", "ProdutosController:estoqueBaixo");
$router->get("/produtos/buscar/{id}", "ProdutosController:buscarProduto");
$router->post("/produtos/entrada-estoque", "ProdutosController:entradaEstoque");
$router->get("/produtos/exportar-estoque-baixo", "ProdutosController:exportarEstoqueBaixo");