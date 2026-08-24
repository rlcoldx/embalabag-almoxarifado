<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Conferencia\ConferenciaRecebimento;
use Agencia\Close\Models\Recebimento\NotaFiscalEletronica;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;

class ConferenciaApiController extends Controller
{
    public function buscarItem(array $params): void
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'visualizar')) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão para conferir produtos.']);
            return;
        }

        $nfeId = (int) ($_GET['nfe_id'] ?? 0);
        $sku = trim($_GET['sku'] ?? '');

        if ($nfeId <= 0 || $sku === '') {
            $this->responseJson(['success' => false, 'message' => 'NFE e SKU são obrigatórios.']);
            return;
        }

        $nfe = new NotaFiscalEletronica();
        $item = $nfe->getItemBySkuForNfe($nfeId, $sku)->getResult();

        if (!$item) {
            $this->responseJson(['success' => false, 'message' => 'Produto não encontrado nesta NF-e.']);
            return;
        }

        $item = $item[0];
        $conferencia = new ConferenciaRecebimento();
        $existente = $conferencia->getConferenciaExistente(
            $nfeId,
            (int) $item['produto_id'],
            (int) $item['variacao_id'],
            (int) $item['item_nfe_id']
        )->getResult();

        $this->responseJson([
            'success' => true,
            'item' => [
                'item_nfe_id' => (int) $item['item_nfe_id'],
                'produto_id' => (int) $item['produto_id'],
                'variacao_id' => (int) $item['variacao_id'],
                'nome_produto' => $item['nome_produto'],
                'sku' => $item['sku'],
                'categoria' => $item['categoria'] ?? '',
                'tamanho' => $item['tamanho'] ?? '',
                'cor' => $item['cor'] ?? '',
                'quantidade_prevista' => (int) $item['quantidade'],
                'conferencia_existente' => $existente[0] ?? null,
            ],
        ]);
    }

    public function conferirItem(array $params): void
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'criar')) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão para salvar conferências.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $nfeId = (int) ($input['nfe_id'] ?? 0);
        $produtoId = (int) ($input['produto_id'] ?? 0);
        $variacaoId = (int) ($input['variacao_id'] ?? 0);
        $itemNfeId = (int) ($input['item_nfe_id'] ?? 0);

        if ($nfeId <= 0 || $produtoId <= 0 || $variacaoId <= 0) {
            $this->responseJson(['success' => false, 'message' => 'Dados incompletos para conferência.']);
            return;
        }

        $quantidadePrevista = (int) ($input['quantidade_prevista'] ?? 0);
        $quantidadeRecebida = (int) ($input['quantidade_recebida'] ?? 0);
        $quantidadeConferida = (int) ($input['quantidade_conferida'] ?? $quantidadeRecebida);
        $statusQualidade = $input['status_qualidade'] ?? 'pendente';
        $statusIntegridade = $input['status_integridade'] ?? 'integro';
        $usuarioId = ResponsavelHelper::idFromPost(
            'usuario_conferente_id',
            (int) ($input['usuario_conferente_id'] ?? ($_SESSION[BASE . 'user_id'] ?? 0))
        );

        if ($quantidadeRecebida < 0 || $statusQualidade === '' || $statusIntegridade === '') {
            $this->responseJson(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
            return;
        }

        $conferencia = new ConferenciaRecebimento();
        $existente = $conferencia->getConferenciaExistente($nfeId, $produtoId, $variacaoId, $itemNfeId ?: null)->getResult();

        $dados = [
            'nfe_id' => $nfeId,
            'item_nfe_id' => $itemNfeId > 0 ? $itemNfeId : null,
            'produto_id' => $produtoId,
            'variacao_id' => $variacaoId,
            'quantidade_prevista' => $quantidadePrevista,
            'quantidade_recebida' => $quantidadeRecebida,
            'quantidade_conferida' => $quantidadeConferida,
            'status_qualidade' => $statusQualidade,
            'status_integridade' => $statusIntegridade,
            'observacoes_qualidade' => $input['observacoes_qualidade'] ?? '',
            'observacoes_integridade' => $input['observacoes_integridade'] ?? '',
            'usuario_conferente_id' => $usuarioId,
            'status_conferencia' => 'concluida',
            'data_conferencia' => date('Y-m-d H:i:s'),
        ];

        try {
            if ($existente) {
                $conferenciaId = (int) $existente[0]['id'];
                $conferencia->atualizarConferencia($conferenciaId, $dados);
            } else {
                $result = $conferencia->criarConferencia($dados);
                $conferenciaId = (int) $result->getResult();
            }

            if ($conferenciaId <= 0) {
                $this->responseJson(['success' => false, 'message' => 'Não foi possível salvar a conferência.']);
                return;
            }

            $conferencia->registrarHistorico([
                'conferencia_id' => $conferenciaId,
                'acao' => $existente ? 'atualizacao_rapida' : 'conferencia_rapida',
                'dados_novos' => json_encode($dados),
                'usuario_id' => $usuarioId,
            ]);

            $nfe = new NotaFiscalEletronica();
            if ($nfe->todosItensConferidos($nfeId)) {
                $nfe->updateStatus($nfeId, 'conferida');
            }

            $this->responseJson([
                'success' => true,
                'message' => 'Conferência salva com sucesso.',
                'conferencia_id' => $conferenciaId,
                'nfe_conferida' => $nfe->todosItensConferidos($nfeId),
            ]);
        } catch (\Exception $e) {
            $this->responseJson(['success' => false, 'message' => 'Erro ao salvar conferência: ' . $e->getMessage()]);
        }
    }

    public function relatorio(array $params): void
    {
        $this->checkSession();
        $this->setParams($params);

        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('conferencia', 'visualizar')) {
            $this->responseJson(['success' => false, 'message' => 'Sem permissão para visualizar relatórios.']);
            return;
        }

        $filtros = [
            'periodo' => $_GET['periodo'] ?? '',
            'status' => $_GET['status'] ?? '',
            'fornecedor' => trim($_GET['fornecedor'] ?? ''),
        ];

        $conferencia = new ConferenciaRecebimento();
        $relatorio = $conferencia->gerarRelatorioConferencia($filtros);
        $graficos = $conferencia->getGraficosRelatorio($filtros);
        $estatisticas = $conferencia->getEstatisticasConferencia();

        $this->responseJson([
            'success' => true,
            'estatisticas' => $estatisticas,
            'dados' => array_map(function (array $row) {
                return [
                    'id' => $row['id'],
                    'numero_nfe' => $row['numero_nfe'] ?? '-',
                    'fornecedor_nome' => $row['fornecedor_nome'] ?? '-',
                    'produto_nome' => $row['produto_nome'] ?? '-',
                    'status_conferencia' => $row['status_conferencia'] ?? 'pendente',
                    'status_qualidade' => $row['status_qualidade'] ?? 'pendente',
                    'data_conferencia' => $row['data_conferencia'] ?? $row['created_at'],
                    'usuario_nome' => $row['usuario_nome'] ?? '-',
                ];
            }, $relatorio['dados']),
            'graficos' => $graficos,
            'resumo' => [
                'total_registros' => $relatorio['total_registros'],
                'conferencias_aprovadas' => $relatorio['conferencias_aprovadas'],
                'conferencias_rejeitadas' => $relatorio['conferencias_rejeitadas'],
            ],
        ]);
    }
}
