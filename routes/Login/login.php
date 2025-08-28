<?php
$router->namespace("Agencia\Close\Controllers\Login");
$router->get("/login", "LoginController:index");
$router->post("/login", "LoginController:sign");
$router->get("/logout", "LoginController:logout");
$router->post("/check-permission", "LoginController:checkPermission");