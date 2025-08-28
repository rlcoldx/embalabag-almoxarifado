<?php

session_start();
date_default_timezone_set('America/Sao_Paulo');

// Configurar exibição de erros para desenvolvimento
error_reporting(1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'vendor/autoload.php';
require __DIR__ . '/routes/routes.php';