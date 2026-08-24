<?php

namespace Agencia\Close\Controllers\Expedicao;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Helpers\QrCode\QrCodeGenerator;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Models\Armazenagem\Armazenagem;
use Agencia\Close\Models\Avarias\Avaria;
use Agencia\Close\Models\Etiqueta\EtiquetaInterna;
use Agencia\Close\Models\Expedicao\Expedicao;
use Agencia\Close\Models\Expedicao\Romaneio;
use Agencia\Close\Models\Pedidos\Pedidos;
use Agencia\Close\Services\Sige\SigeService;

class ExpedicaoController extends Controller
{
    private PermissionHelper $permissionHelper;
    private Expedicao $expedicao;

    public function __construct($router)
    {
        parent::__construct($router);
        $this->permissionHelper = new PermissionHelper();
        $this->expedicao = new Expedicao();
    }

    public function index(array $params)
    {
        if (!$this->autorizar($params, 'visualizar')) {
            return;
        }

        $this->render('pages/expedicao/index.twig', [
            'menu' => 'expedicao',
            'aprovados' => count($this->expedicao->getPedidosAprovados()),
            'separacao' => count($this->expedicao->getPedidosPorEtapa('em_separacao')),
            'embalagem' => count($this->expedicao->getPedidosPorEtapa('separado')) + count($this->expedicao->getPedidosPorEtapa('em_embalagem')),
            'conferencia' => count($this->expedicao->getPedidosPorEtapa('embalado')),
            'romaneios' => count((new Romaneio())->listar()),
            'sige_ok' => SigeService::isConfigured(),
        ]);
    }

    public function aprovados(array $params)
    {
        if (!$this->autorizar($params, 'visualizar')) {
            return;
        }

        $this->render('pages/expedicao/lista.twig', [
            'menu' => 'expedicao_aprovados',
            'titulo' => 'Pedidos aprovados',
            'descricao' => 'Pedidos prontos para separação.',
            'pedidos' => $this->expedicao->getPedidosAprovados(),
            'acao_url' => DOMAIN . '/expedicao/separacao/iniciar',
            'acao_label' => 'Iniciar separação',
            'mostrar_etiqueta' => true,
        ]);
    }

    public function iniciarSeparacao(array $params)
    {
        if (!$this->autorizar($params, 'separar')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $this->expedicao->iniciarSeparacao($id, (int)($_SESSION[BASE . 'user_id'] ?? 0));
        header('Location: ' . DOMAIN . '/expedicao/separacao/' . $id);
        exit;
    }

    public function separacaoLista(array $params)
    {
        if (!$this->autorizar($params, 'separar')) {
            return;
        }

        $this->render('pages/expedicao/lista.twig', [
            'menu' => 'expedicao_separacao',
            'titulo' => 'Separação',
            'descricao' => 'Pedidos em picking.',
            'pedidos' => $this->expedicao->getPedidosPorEtapa('em_separacao'),
            'acao_url' => DOMAIN . '/expedicao/separacao',
            'acao_label' => 'Abrir',
            'mostrar_etiqueta' => true,
        ]);
    }

    public function separacao(array $params)
    {
        if (!$this->autorizar($params, 'separar')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $pedido = $this->expedicao->getPedidoCompleto($id);
        if (!$pedido) {
            echo 'Pedido não encontrado.';
            return;
        }

        $this->render('pages/expedicao/separacao.twig', [
            'menu' => 'expedicao_separacao',
            'pedido' => $pedido,
            'itens' => $this->expedicao->getItensSeparacao($id),
            'enderecos' => $this->enderecosDisponiveis(),
        ]);
    }

    public function separarItem(array $params)
    {
        if (!$this->autorizarJson('separar')) {
            return;
        }

        $this->expedicao->marcarItemSeparado((int)$_POST['item_id'], (int)($_SESSION[BASE . 'user_id'] ?? 0));
        $this->responseJson(['success' => true]);
    }

    public function trocarEndereco(array $params)
    {
        if (!$this->autorizarJson('separar')) {
            return;
        }

        $novo = (int)($_POST['armazenagem_id'] ?? 0);
        if (!$this->podeUsarEndereco($novo)) {
            $this->responseJson(['success' => false, 'message' => 'Endereço bloqueado. Somente o administrador pode usá-lo.']);
            return;
        }

        $this->expedicao->trocarEndereco((int)$_POST['item_id'], $novo);
        $this->responseJson(['success' => true, 'message' => 'Endereço da separação atualizado.']);
    }

    public function concluirSeparacao(array $params)
    {
        if (!$this->autorizar($params, 'separar')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        if (!$this->expedicao->separacaoCompleta($id)) {
            $_SESSION['error'] = 'Ainda há itens pendentes na separação.';
            header('Location: ' . DOMAIN . '/expedicao/separacao/' . $id);
            exit;
        }

        $this->expedicao->atualizarStatus($id, 'separado');
        header('Location: ' . DOMAIN . '/expedicao/embalagem/' . $id);
        exit;
    }

    public function embalagemLista(array $params)
    {
        if (!$this->autorizar($params, 'embalar')) {
            return;
        }

        $pedidos = array_merge(
            $this->expedicao->getPedidosPorEtapa('separado'),
            $this->expedicao->getPedidosPorEtapa('em_embalagem')
        );

        $this->render('pages/expedicao/lista.twig', [
            'menu' => 'expedicao_embalagem',
            'titulo' => 'Embalagem',
            'descricao' => 'Pedidos separados prontos para embalar.',
            'pedidos' => $pedidos,
            'acao_url' => DOMAIN . '/expedicao/embalagem',
            'acao_label' => 'Embalar',
        ]);
    }

    public function embalagem(array $params)
    {
        if (!$this->autorizar($params, 'embalar')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $this->expedicao->atualizarStatus($id, 'em_embalagem');
        $this->render('pages/expedicao/etapa.twig', [
            'menu' => 'expedicao_embalagem',
            'titulo' => 'Embalagem',
            'pedido' => $this->expedicao->getPedidoCompleto($id),
            'itens' => $this->expedicao->getItensSeparacao($id),
            'acao' => DOMAIN . '/expedicao/embalagem/concluir/' . $id,
            'acao_label' => 'Concluir embalagem',
            'texto' => 'Confira os itens separados, embale e registre a conclusão.',
        ]);
    }

    public function concluirEmbalagem(array $params)
    {
        if (!$this->autorizar($params, 'embalar')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $this->expedicao->atualizarStatus($id, 'embalado');
        header('Location: ' . DOMAIN . '/expedicao/conferencia/' . $id);
        exit;
    }

    public function conferenciaLista(array $params)
    {
        if (!$this->autorizar($params, 'conferir')) {
            return;
        }

        $this->render('pages/expedicao/lista.twig', [
            'menu' => 'expedicao_conferencia',
            'titulo' => 'Conferência de saída',
            'descricao' => 'Pedidos embalados prontos para conferir destino e SIGE.',
            'pedidos' => $this->expedicao->getPedidosPorEtapa('embalado'),
            'acao_url' => DOMAIN . '/expedicao/conferencia',
            'acao_label' => 'Conferir',
        ]);
    }

    public function conferencia(array $params)
    {
        if (!$this->autorizar($params, 'conferir')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $this->render('pages/expedicao/conferencia.twig', [
            'menu' => 'expedicao_conferencia',
            'pedido' => $this->expedicao->getPedidoCompleto($id),
            'leituras' => $this->expedicao->getConferencias($id),
            'itens' => $this->expedicao->getItensSeparacao($id),
        ]);
    }

    public function conferirCodigo(array $params)
    {
        if (!$this->autorizarJson('conferir')) {
            return;
        }

        $pedidoId = (int)($_POST['pedido_id'] ?? 0);
        $codigo = trim((string)($_POST['codigo'] ?? ''));
        $tipo = (string)($_POST['tipo'] ?? 'item');
        $pedido = $this->expedicao->getPedidoCompleto($pedidoId);

        if (!$pedido || $codigo === '') {
            $this->responseJson(['success' => false, 'message' => 'Pedido ou código inválido.']);
            return;
        }

        $ok = true;
        $mensagem = 'Código conferido.';
        if ($tipo === 'sige') {
            $ok = in_array($codigo, array_filter([$pedido['codigoSige'] ?? '', $pedido['codigo'] ?? '', $pedido['etiqueta_interna'] ?? '']), true);
            $mensagem = $ok ? 'Pedido SIGE conferido.' : 'Código SIGE não confere com este pedido.';
        }
        if ($tipo === 'destino') {
            $esperado = array_filter([$pedido['base_destino'] ?? '', $pedido['etiqueta_cia_aerea'] ?? '', $pedido['numero_re'] ?? '']);
            $ok = in_array($codigo, $esperado, true) || strcasecmp((string)($pedido['base_destino'] ?? ''), $codigo) === 0;
            $mensagem = $ok ? 'Destino / etiqueta da cia aérea conferidos.' : 'Destino não confere. Esperado: ' . ($pedido['base_destino'] ?: 'não informado');
        }

        if ($ok) {
            $this->expedicao->registrarConferencia($pedidoId, $tipo, $codigo, (int)($_SESSION[BASE . 'user_id'] ?? 0));
        }

        $this->responseJson(['success' => $ok, 'message' => $mensagem]);
    }

    public function concluirConferencia(array $params)
    {
        if (!$this->autorizar($params, 'conferir')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $this->expedicao->atualizarStatus($id, 'conferido');
        header('Location: ' . DOMAIN . '/expedicao/romaneios/novo?pedido_id=' . $id);
        exit;
    }

    public function romaneios(array $params)
    {
        if (!$this->autorizar($params, 'romaneio')) {
            return;
        }

        $this->render('pages/expedicao/romaneios.twig', [
            'menu' => 'expedicao_romaneios',
            'romaneios' => (new Romaneio())->listar(),
        ]);
    }

    public function romaneioNovo(array $params)
    {
        if (!$this->autorizar($params, 'romaneio')) {
            return;
        }

        $prontos = array_merge(
            $this->expedicao->getPedidosPorEtapa('conferido'),
            $this->expedicao->getPedidosPorEtapa('embalado')
        );

        $this->render('pages/expedicao/romaneio-form.twig', [
            'menu' => 'expedicao_romaneios',
            'pedidos' => $prontos,
            'companhias' => $this->expedicao->getCompanhias(),
            'pedido_pre' => (int)($_GET['pedido_id'] ?? 0),
        ]);
    }

    public function romaneioSalvar(array $params)
    {
        if (!$this->autorizar($params, 'romaneio')) {
            return;
        }

        $pedidos = $_POST['pedidos'] ?? [];
        if ($pedidos === []) {
            $_SESSION['error'] = 'Selecione ao menos um pedido.';
            header('Location: ' . DOMAIN . '/expedicao/romaneios/novo');
            exit;
        }

        $romaneio = new Romaneio();
        $id = $romaneio->criar([
            'codigo' => $romaneio->proximoCodigo(),
            'tipo' => $_POST['tipo'] ?? 'cia_aerea',
            'companhia_id' => $_POST['companhia_id'] !== '' ? (int)$_POST['companhia_id'] : null,
            'voo' => $_POST['voo'] ?? '',
            'base_destino' => $_POST['base_destino'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'usuario_id' => $_SESSION[BASE . 'user_id'] ?? null,
            'status' => 'aberto',
        ], $pedidos);

        header('Location: ' . DOMAIN . '/expedicao/romaneios/' . $id);
        exit;
    }

    public function romaneioShow(array $params)
    {
        if (!$this->autorizar($params, 'romaneio')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $romaneio = new Romaneio();
        $this->render('pages/expedicao/romaneio-show.twig', [
            'menu' => 'expedicao_romaneios',
            'romaneio' => $romaneio->getById($id),
            'itens' => $romaneio->getItens($id),
            'imprimir' => false,
        ]);
    }

    public function romaneioImprimir(array $params)
    {
        if (!$this->autorizar($params, 'romaneio')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $romaneio = new Romaneio();
        $this->render('pages/expedicao/romaneio-show.twig', [
            'menu' => 'expedicao_romaneios',
            'romaneio' => $romaneio->getById($id),
            'itens' => $romaneio->getItens($id),
            'imprimir' => true,
        ]);
    }

    public function romaneioEnviar(array $params)
    {
        if (!$this->autorizar($params, 'romaneio')) {
            return;
        }

        (new Romaneio())->marcarEnviado((int)($params['id'] ?? 0));
        header('Location: ' . DOMAIN . '/expedicao/romaneios/' . (int)$params['id']);
        exit;
    }

    public function bipagem(array $params)
    {
        if (!$this->autorizar($params, 'bipar')) {
            return;
        }

        $this->render('pages/expedicao/bipagem.twig', [
            'menu' => 'expedicao_bipagem',
            'sige_ok' => SigeService::isConfigured(),
        ]);
    }

    public function bipar(array $params)
    {
        if (!$this->autorizarJson('bipar')) {
            return;
        }

        $sige = new SigeService();
        $codigoSige = trim((string)($_POST['codigo_sige'] ?? ''));
        $etiquetaCia = trim((string)($_POST['etiqueta_cia'] ?? ''));
        $consulta = $sige->buscarPedido($codigoSige);

        if (!$consulta['success'] && !empty($_POST['importar'])) {
            $consulta = $sige->importarPedido($codigoSige);
        }

        if (!$consulta['success'] || empty($consulta['pedido'])) {
            $this->responseJson([
                'success' => false,
                'message' => $consulta['message'] ?? 'Pedido SIGE não encontrado. Importe do SIGE ou cadastre o código no pedido.',
            ]);
            return;
        }

        $pedido = $consulta['pedido'];
        if ($etiquetaCia !== '') {
            (new Pedidos())->updatePedido((int)$pedido['id'], ['etiqueta_cia_aerea' => $etiquetaCia]);
            $this->expedicao->registrarConferencia((int)$pedido['id'], 'destino', $etiquetaCia, (int)($_SESSION[BASE . 'user_id'] ?? 0));
            $this->expedicao->registrarConferencia((int)$pedido['id'], 'sige', $codigoSige, (int)($_SESSION[BASE . 'user_id'] ?? 0));
        }

        $this->responseJson([
            'success' => true,
            'message' => 'Pedido SIGE localizado' . ($etiquetaCia !== '' ? ' e etiqueta da cia aérea vinculada.' : '.'),
            'pedido' => $pedido,
            'redirect' => DOMAIN . '/pedidos/show/' . $pedido['id'],
        ]);
    }

    public function encomendas(array $params)
    {
        if (!$this->autorizarModulo($params, 'encomendas', 'visualizar')) {
            return;
        }

        $this->render('pages/expedicao/encomendas.twig', [
            'menu' => 'expedicao_encomendas',
            'encomendas' => $this->expedicao->getEncomendas(),
        ]);
    }

    public function salvarEncomenda(array $params)
    {
        if (!$this->autorizarJsonModulo('encomendas', 'editar')) {
            return;
        }

        $this->expedicao->atualizarPrevisaoEncomenda(
            (int)$_POST['pedido_id'],
            (int)$_POST['produto_id'],
            trim((string)$_POST['previsao_chegada'])
        );
        $this->responseJson(['success' => true, 'message' => 'Previsão atualizada.']);
    }

    public function avarias(array $params)
    {
        if (!$this->autorizarModulo($params, 'avarias', 'visualizar')) {
            return;
        }

        $this->render('pages/expedicao/avarias.twig', [
            'menu' => 'expedicao_avarias',
            'avarias' => (new Avaria())->listar(),
            'produtos' => $this->listarProdutos(),
            'enderecos' => $this->enderecosDisponiveis(),
        ]);
    }

    public function salvarAvaria(array $params)
    {
        if (!$this->autorizarModulo($params, 'avarias', 'criar')) {
            return;
        }

        (new Avaria())->criar([
            'produto_id' => $_POST['produto_id'] !== '' ? (int)$_POST['produto_id'] : null,
            'armazenagem_id' => $_POST['armazenagem_id'] !== '' ? (int)$_POST['armazenagem_id'] : null,
            'tipo' => $_POST['tipo'] ?? 'produto',
            'severidade' => $_POST['severidade'] ?? 'media',
            'descricao' => $_POST['descricao'] ?? '',
            'quantidade_afetada' => (int)($_POST['quantidade_afetada'] ?? 0),
            'status' => 'aberta',
            'data_ocorrencia' => date('Y-m-d H:i:s'),
            'usuario_reportou' => $_SESSION[BASE . 'user_id'] ?? 1,
            'observacoes' => $_POST['observacoes'] ?? '',
        ]);

        header('Location: ' . DOMAIN . '/expedicao/avarias');
        exit;
    }

    public function atualizarAvaria(array $params)
    {
        if (!$this->autorizarModulo($params, 'avarias', 'editar')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $status = $_POST['status'] ?? 'em_analise';
        $data = [
            'status' => $status,
            'acao_corretiva' => $_POST['acao_corretiva'] ?? '',
            'usuario_responsavel' => $_SESSION[BASE . 'user_id'] ?? null,
        ];
        if (in_array($status, ['resolvida', 'fechada'], true)) {
            $data['data_resolucao'] = date('Y-m-d H:i:s');
        }
        (new Avaria())->atualizar($id, $data);
        header('Location: ' . DOMAIN . '/expedicao/avarias');
        exit;
    }

    public function relatorioSeparacao(array $params)
    {
        if (!$this->autorizar($params, 'visualizar')) {
            return;
        }

        $pedidos = array_merge(
            $this->expedicao->getPedidosPorEtapa('em_separacao'),
            $this->expedicao->getPedidosPorEtapa('separado')
        );
        foreach ($pedidos as &$pedido) {
            $pedido['itens'] = $this->expedicao->getItensSeparacao((int)$pedido['id']);
        }
        unset($pedido);

        $this->render('pages/expedicao/relatorio-separacao.twig', [
            'menu' => 'expedicao_relatorio',
            'pedidos' => $pedidos,
            'imprimir' => isset($_GET['print']),
        ]);
    }

    public function gerarEtiqueta(array $params)
    {
        if (!$this->autorizar($params, 'visualizar')) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $pedido = $this->expedicao->getPedidoCompleto($id);
        if (!$pedido) {
            echo 'Pedido não encontrado.';
            return;
        }

        $etiqueta = new EtiquetaInterna();
        $codigo = $pedido['etiqueta_interna'] ?: $etiqueta->gerarCodigoEtiqueta('pedido', $id);
        if (empty($pedido['etiqueta_interna'])) {
            $etiqueta->createEtiqueta([
                'codigo' => $codigo,
                'tipo_etiqueta' => 'pedido',
                'referencia_id' => $id,
                'referencia_tipo' => 'pedido',
                'conteudo' => json_encode([
                    'tipo' => 'Pedido',
                    'codigo' => $pedido['codigo'],
                    'sige' => $pedido['codigoSige'] ?? '',
                    'destino' => $pedido['base_destino'] ?? '',
                ]),
                'codigo_barras' => $codigo,
                'qr_code' => $codigo,
                'usuario_criacao' => $_SESSION[BASE . 'user_id'] ?? 1,
                'status' => 'criada',
            ]);
            (new Pedidos())->updatePedido($id, ['etiqueta_interna' => $codigo]);
            $pedido['etiqueta_interna'] = $codigo;
        }

        $this->render('pages/expedicao/etiqueta-pedido.twig', [
            'menu' => 'expedicao',
            'pedido' => $pedido,
            'qr_code' => QrCodeGenerator::toDataUri($pedido['etiqueta_interna']),
        ]);
    }

    private function autorizar(array $params, string $acao): bool
    {
        $this->checkSession();
        $this->setParams($params);
        if (
            !$this->permissionHelper->userHasPermission('expedicao', $acao)
            && !$this->permissionHelper->userHasPermission('pedidos', 'visualizar')
        ) {
            echo 'Sem permissão para acessar a expedição.';
            return false;
        }
        return true;
    }

    private function autorizarJson(string $acao): bool
    {
        $this->checkSession();
        if (
            !$this->permissionHelper->userHasPermission('expedicao', $acao)
            && !$this->permissionHelper->userHasPermission('pedidos', 'visualizar')
        ) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão.']);
            return false;
        }
        return true;
    }

    private function autorizarModulo(array $params, string $modulo, string $acao): bool
    {
        $this->checkSession();
        $this->setParams($params);
        if (!$this->permissionHelper->userHasPermission($modulo, $acao) && !$this->permissionHelper->userHasPermission('expedicao', 'visualizar')) {
            echo 'Sem permissão.';
            return false;
        }
        return true;
    }

    private function autorizarJsonModulo(string $modulo, string $acao): bool
    {
        $this->checkSession();
        if (!$this->permissionHelper->userHasPermission($modulo, $acao) && !$this->permissionHelper->userHasPermission('expedicao', 'visualizar')) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão.']);
            return false;
        }
        return true;
    }

    private function enderecosDisponiveis(): array
    {
        $armazenagem = new Armazenagem();
        if (($_SESSION[BASE . 'user_tipo'] ?? '') === '1') {
            return $armazenagem->getAllArmazenagens()->getResult() ?: [];
        }
        return $armazenagem->getArmazenagensAtivas()->getResult() ?: [];
    }

    private function podeUsarEndereco(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $armazenagem = (new Armazenagem())->getArmazenagemById($id)->getResult()[0] ?? null;
        if (!$armazenagem) {
            return false;
        }
        if (($armazenagem['status'] ?? '') === 'bloqueado') {
            return ($_SESSION[BASE . 'user_tipo'] ?? '') === '1';
        }
        return ($armazenagem['status'] ?? '') === 'ativo';
    }

    private function listarProdutos(): array
    {
        $read = new \Agencia\Close\Conn\Read();
        $read->FullRead("SELECT id, nome, SKU FROM produtos WHERE status <> 'Deletado' ORDER BY nome LIMIT 300");
        return $read->getResult() ?: [];
    }
}
