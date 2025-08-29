<?php

// Rotas para Usuários
$router->namespace("Agencia\Close\Controllers\Users");

// Rotas específicas com prioridade máxima (devem vir ANTES das rotas com parâmetros)
$router->get("/users/create", "UsersController:create");
$router->post("/users/store", "UsersController:store");
$router->get("/cargos/create", "CargosController:create");
$router->post("/cargos/store", "CargosController:store");

// Rota principal
$router->get("/users", "UsersController:index");
$router->get("/cargos", "CargosController:index");

// Rotas com parâmetros por último (devem vir DEPOIS das rotas específicas)
$router->get("/users/edit/{id}", "UsersController:edit");
$router->post("/users/update/{id}", "UsersController:update");
$router->post("/users/delete/{id}", "UsersController:delete");
$router->get("/cargos/edit/{id}", "CargosController:edit");
$router->post("/cargos/update/{id}", "CargosController:update");
$router->post("/cargos/delete/{id}", "CargosController:delete");

// Rotas para Fornecedores
$router->get("/usuarios/fornecedores", "UsersController:getFornecedores");