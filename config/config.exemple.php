<?php

define('BASE', 'bagalmo_');
define('DOMAIN', 'http://localhost:8000');
define('PATH', 'http://localhost:8000');
define('NAME', 'EmbalaBag Almoxarifado');
define('PRODUCTION', false);

//// CONFIGURAÇÕES DO BANCO PRINCIPAL ########################
define('HOST_MAIN', '');
define('USER_MAIN', '');
define('PASS_MAIN', '');
define('DBSA_MAIN', '');

//// CONFIGURAÇÕES DE EMAIL ########################
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', '587');
define('MAIL_SECURE', 'tls'); // tls (587) ou ssl (465)
define('MAIL_USER', '');
define('MAIL_PASS', '');
define('MAIL_FROM', '');
define('MAIL_FROM_NAME', NAME);

//// CONFIGURAÇÕES DO SIGE CLOUD ########################
define('SIGE_API_URL', 'https://api.sigecloud.com.br/request');
define('SIGE_AUTHORIZATIONTOKEN', '');
define('SIGE_USER', '');
define('SIGE_APP', 'API');

//// CONFIGURAÇÕES DE COOKIE ########################
define('COOKIE_EXPIRE', '30');
define('COOKIE_PATH', '/');

define('VERSION', trim(@file_get_contents(__DIR__ . '/../.git/refs/heads/main')) ?: '1.0.0');
