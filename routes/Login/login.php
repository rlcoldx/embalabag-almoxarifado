<?php
$router->namespace("Agencia\Close\Controllers\Login");
$router->get("/login", "LoginController:index");
$router->post("/login", "LoginController:sign");
$router->get("/login/recover", "LoginController:recover");
$router->post("/login/recover", "LoginController:recoverSend");
$router->get("/login/reset/{token}", "LoginController:reset");
$router->post("/login/reset", "LoginController:resetSave");
$router->get("/logout", "LoginController:logout");
$router->post("/check-permission", "LoginController:checkPermission");