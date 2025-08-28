<?php
// Rota para executar migrations
$router->namespace("Agencia\Close\Controllers");
$router->get("/migrate", "MigrationController:migrate");