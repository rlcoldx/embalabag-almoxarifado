<?php

namespace Agencia\Close\Controllers\Conferencia;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Conferencia\ConferenciaRecebimento;
use Agencia\Close\Models\Recebimento\NotaFiscalEletronica;
use Agencia\Close\Helpers\User\PermissionHelper;

class ConferenciaRecebimentoController extends Controller
{
    /**
     * Listar todas as conferências
     */
    public function index($params)
    {
        $this->setParams($params);

        $this->checkSession();
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'visualizar')) {
            echo 'Sem permissão para visualizar conferências.';
            return;
        }

        try {
            $conferencia = new ConferenciaRecebimento();
            $conferencias = $conferencia->getAllConferencias();

            $this->render('pages/conferencia/index.twig', [
                'conferencias' => $conferencias->getResult() ?? [],
                'estatisticas' => [], // Simplificar para não depender de estatísticas
                'menu' => 'conferencia'
            ]);
        } catch (\PDOException $e) {
            // Log do erro de banco para debug
            error_log('Erro de banco na conferência: ' . $e->getMessage());
            
            // Renderizar página com erro de banco
            $this->render('pages/conferencia/index.twig', [
                'conferencias' => [],
                'estatisticas' => [],
                'menu' => 'conferencia',
                'error' => 'Erro de conexão com banco de dados: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Log do erro geral para debug
            error_log('Erro geral na conferência: ' . $e->getMessage());
            
            // Renderizar página com erro
            $this->render('pages/conferencia/index.twig', [
                'conferencias' => [],
                'estatisticas' => [],
                'menu' => 'conferencia',
                'error' => 'Erro ao carregar conferências: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Criar nova conferência
     */
    public function create($params)
    {
        $this->setParams($params);
        $this->checkSession();
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'criar')) {
            echo 'Sem permissão para criar conferências.';
            return;
        }

        $nfe = new NotaFiscalEletronica();
        $nfes = $nfe->getPendentesConferencia();

        $this->render('pages/conferencia/create.twig', [
            'nfes' => $nfes->getResult() ?? [],
            'menu' => 'conferencia_new'
        ]);
    }

    /**
     * Iniciar conferência de uma NFE
     */
    public function iniciarConferencia(array $params)
    {
        $this->setParams($params);
        $this->checkSession();
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'criar')) {
            echo 'Sem permissão para criar conferências.';
            return;
        }

        $nfeId = $params['nfe_id'] ?? null;
        if (!$nfeId) {
            echo 'ID da NFE não informado.';
            return;
        }

        $nfe = new NotaFiscalEletronica();
        $nfeData = $nfe->getByNumero($nfeId);
        
        if (!$nfeData->getResult()) {
            echo 'NFE não encontrada.';
            return;
        }

        $nfeInfo = $nfeData->getResult()[0];
        $itens = $nfe->getItens($nfeId);

        $this->render('pages/conferencia/conferir.twig', [
            'nfe' => $nfeInfo,
            'itens' => $itens->getResult() ?? [],
            'menu' => 'conferencia_new'
        ]);
    }


    /**
     * Mostrar conferência específica
     */
    public function show(array $params)
    {
        $this->setParams($params);
        $this->checkSession();

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'visualizar')) {
            echo 'Sem permissão para visualizar conferências.';
            return;
        }

        $conferenciaId = $params['id'] ?? null;
        if (!$conferenciaId) {
            echo 'ID da conferência não informado.';
            return;
        }

        $conferencia = new ConferenciaRecebimento();
        $conferenciaData = $conferencia->getConferenciaById($conferenciaId);

        if (!$conferenciaData->getResult()) {
            echo 'Conferência não encontrada.';
            return;
        }

        $conferenciaInfo = $conferenciaData->getResult()[0];
        $historico = $conferencia->getHistoricoConferencia($conferenciaId);

        $this->render('pages/conferencia/show.twig', [
            'conferencia' => $conferenciaInfo,
            'historico' => $historico->getResult() ?? [],
            'menu' => 'conferencia'
        ]);
    }

    /**
     * Salvar conferência
     */
    public function store($params)
    {
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'criar')) {
            echo 'Sem permissão para criar conferências.';
            return;
        }

        $nfeId = $_POST['nfe_id'] ?? null;
        $itens = $_POST['itens'] ?? [];

        if (!$nfeId || empty($itens)) {
            echo 'Dados inválidos para conferência.';
            return;
        }

        $conferencia = new ConferenciaRecebimento();
        $usuarioId = $_SESSION['user_id'] ?? 1;

        try {
            foreach ($itens as $item) {
                $dadosConferencia = [
                    'nfe_id' => $nfeId,
                    'produto_id' => $item['produto_id'],
                    'variacao_id' => $item['variacao_id'],
                    'quantidade_prevista' => $item['quantidade_prevista'],
                    'quantidade_recebida' => $item['quantidade_recebida'],
                    'quantidade_conferida' => $item['quantidade_conferida'],
                    'status_qualidade' => $item['status_qualidade'],
                    'status_integridade' => $item['status_integridade'],
                    'observacoes_qualidade' => $item['observacoes_qualidade'] ?? '',
                    'observacoes_integridade' => $item['observacoes_integridade'] ?? '',
                    'usuario_conferente_id' => $usuarioId,
                    'status_conferencia' => 'concluida',
                    'data_conferencia' => date('Y-m-d H:i:s')
                ];

                $result = $conferencia->criarConferencia($dadosConferencia);
                
                if ($result->getResult()) {
                    // Registrar no histórico
                    $conferencia->registrarHistorico([
                        'conferencia_id' => $result->getResult(),
                        'acao' => 'criacao',
                        'dados_novos' => json_encode($dadosConferencia),
                        'usuario_id' => $usuarioId
                    ]);
                }
            }

            // Redirecionar para lista de conferências
            header("Location: " . DOMAIN . "/conferencia");
            exit;

        } catch (\Exception $e) {
            echo 'Erro ao salvar conferência: ' . $e->getMessage();
        }
    }

    /**
     * Editar conferência
     */
    public function edit(array $params)
    {
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'editar')) {
            echo 'Sem permissão para editar conferências.';
            return;
        }

        $conferenciaId = $params['id'] ?? null;
        if (!$conferenciaId) {
            echo 'ID da conferência não informado.';
            return;
        }

        $conferencia = new ConferenciaRecebimento();
        $conferenciaData = $conferencia->getConferenciaById($conferenciaId);
        
        if (!$conferenciaData->getResult()) {
            echo 'Conferência não encontrada.';
            return;
        }

        $conferenciaInfo = $conferenciaData->getResult()[0];

        $this->render('pages/conferencia/edit.twig', [
            'conferencia' => $conferenciaInfo,
            'menu' => 'conferencia'
        ]);
    }

    /**
     * Atualizar conferência
     */
    public function update(array $params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'editar')) {
            echo 'Sem permissão para editar conferências.';
            return;
        }

        $conferenciaId = $params['id'] ?? null;
        if (!$conferenciaId) {
            echo 'ID da conferência não informado.';
            return;
        }

        $dados = [
            'quantidade_conferida' => $_POST['quantidade_conferida'] ?? 0,
            'status_qualidade' => $_POST['status_qualidade'] ?? 'pendente',
            'status_integridade' => $_POST['status_integridade'] ?? 'integro',
            'observacoes_qualidade' => $_POST['observacoes_qualidade'] ?? '',
            'observacoes_integridade' => $_POST['observacoes_integridade'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $conferencia = new ConferenciaRecebimento();
        $result = $conferencia->atualizarConferencia($conferenciaId, $dados);

        if ($result->getResult()) {
            // Registrar no histórico
            $conferencia->registrarHistorico([
                'conferencia_id' => $conferenciaId,
                'acao' => 'atualizacao',
                'dados_anteriores' => json_encode($_POST['dados_anteriores'] ?? []),
                'dados_novos' => json_encode($dados),
                'usuario_id' => $_SESSION['user_id'] ?? 1
            ]);

            header("Location: " . DOMAIN . "/conferencia/{$conferenciaId}");
            exit;
        } else {
            echo 'Erro ao atualizar conferência.';
        }
    }

    /**
     * Excluir conferência
     */
    public function destroy(array $params)
    {
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'excluir')) {
            echo 'Sem permissão para excluir conferências.';
            return;
        }

        $conferenciaId = $params['id'] ?? null;
        if (!$conferenciaId) {
            echo 'ID da conferência não informado.';
            return;
        }

        $conferencia = new ConferenciaRecebimento();
        
        // Registrar exclusão no histórico antes de excluir
        $conferencia->registrarHistorico([
            'conferencia_id' => $conferenciaId,
            'acao' => 'exclusao',
            'dados_anteriores' => json_encode(['conferencia_id' => $conferenciaId]),
            'usuario_id' => $_SESSION['user_id'] ?? 1
        ]);

        // Redirecionar para lista
        header("Location: " . DOMAIN . "/conferencia");
        exit;
    }

    /**
     * Relatório de conferências
     */
    public function relatorio($params)
    {
        $this->setParams($params);
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'visualizar')) {
            echo 'Sem permissão para visualizar relatórios.';
            return;
        }

        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';

        $conferencia = new ConferenciaRecebimento();
        $estatisticas = $conferencia->getEstatisticasConferencia();

        $this->render('pages/conferencia/relatorio.twig', [
            'estatisticas' => $estatisticas,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'status' => $status,
            'menu' => 'conferencia_relatorio'
        ]);
    }
}
