<?php
$router->namespace("Agencia\Close\Controllers\Home");
$router->get("/", "HomeController:index");
$router->get("/dashboard/produtos", "DashboardController:produtos");
$router->get("/dashboard/armazenagens", "DashboardController:armazenagens");
$router->get("/dashboard/movimentacoes", "DashboardController:movimentacoes");
