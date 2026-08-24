<?php

$router->namespace('Agencia\Close\Controllers\Expedicao');

$router->get('/expedicao', 'ExpedicaoController:index');
$router->get('/expedicao/aprovados', 'ExpedicaoController:aprovados');
$router->get('/expedicao/separacao', 'ExpedicaoController:separacaoLista');
$router->get('/expedicao/separacao/iniciar/{id}', 'ExpedicaoController:iniciarSeparacao');
$router->get('/expedicao/separacao/{id}', 'ExpedicaoController:separacao');
$router->post('/expedicao/separacao/item', 'ExpedicaoController:separarItem');
$router->post('/expedicao/separacao/trocar-endereco', 'ExpedicaoController:trocarEndereco');
$router->post('/expedicao/separacao/concluir/{id}', 'ExpedicaoController:concluirSeparacao');
$router->get('/expedicao/embalagem', 'ExpedicaoController:embalagemLista');
$router->get('/expedicao/embalagem/{id}', 'ExpedicaoController:embalagem');
$router->post('/expedicao/embalagem/concluir/{id}', 'ExpedicaoController:concluirEmbalagem');
$router->get('/expedicao/conferencia', 'ExpedicaoController:conferenciaLista');
$router->get('/expedicao/conferencia/{id}', 'ExpedicaoController:conferencia');
$router->post('/expedicao/conferencia/codigo', 'ExpedicaoController:conferirCodigo');
$router->post('/expedicao/conferencia/concluir/{id}', 'ExpedicaoController:concluirConferencia');
$router->get('/expedicao/romaneios', 'ExpedicaoController:romaneios');
$router->get('/expedicao/romaneios/novo', 'ExpedicaoController:romaneioNovo');
$router->post('/expedicao/romaneios/salvar', 'ExpedicaoController:romaneioSalvar');
$router->get('/expedicao/romaneios/{id}', 'ExpedicaoController:romaneioShow');
$router->get('/expedicao/romaneios/{id}/imprimir', 'ExpedicaoController:romaneioImprimir');
$router->post('/expedicao/romaneios/{id}/enviar', 'ExpedicaoController:romaneioEnviar');
$router->get('/expedicao/bipagem', 'ExpedicaoController:bipagem');
$router->post('/expedicao/bipar', 'ExpedicaoController:bipar');
$router->get('/expedicao/encomendas', 'ExpedicaoController:encomendas');
$router->post('/expedicao/encomendas/salvar', 'ExpedicaoController:salvarEncomenda');
$router->get('/expedicao/avarias', 'ExpedicaoController:avarias');
$router->post('/expedicao/avarias/salvar', 'ExpedicaoController:salvarAvaria');
$router->post('/expedicao/avarias/{id}/status', 'ExpedicaoController:atualizarAvaria');
$router->get('/expedicao/relatorio-separacao', 'ExpedicaoController:relatorioSeparacao');
$router->get('/expedicao/etiqueta/{id}', 'ExpedicaoController:gerarEtiqueta');

$router->get('/cia-aerea', 'CiaAereaController:index');
$router->get('/cia-aerea/romaneios/{id}', 'CiaAereaController:show');
$router->post('/cia-aerea/romaneios/{id}/receber', 'CiaAereaController:receber');
