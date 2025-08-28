<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Conferencia\ConferenciaProduto;
use Agencia\Close\Models\Movimentacao\MovimentacaoInterna;
use Agencia\Close\Helpers\User\PermissionHelper;

class DashboardController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $this->render('pages/recebimento/dashboard/index.twig', [
            'menu' => 'recebimento_dashboard'
        ]);
    }

    public function estatisticas(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar estatísticas.'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $conferencia = new ConferenciaProduto();
        $movimentacao = new MovimentacaoInterna();
        
        // Estatísticas de Notas Fiscais
        $nfPendentes = $notaFiscal->getCountByStatus('pendente');
        $nfRecebidasHoje = $notaFiscal->getCountRecebidasHoje();
        $valorTotalMes = $notaFiscal->getValorTotalMes();
        
        // Estatísticas de Conferência
        $conferenciasPendentes = $conferencia->getCountByStatus('pendente');
        
        // Estatísticas de Movimentação
        $movimentacoesPendentes = $movimentacao->getCountByStatus('pendente');
        
        $estatisticas = [
            'nf_pendentes' => $nfPendentes,
            'nf_recebidas_hoje' => $nfRecebidasHoje,
            'conferencias_pendentes' => $conferenciasPendentes,
            'movimentacoes_pendentes' => $movimentacoesPendentes,
            'valor_total_mes' => $valorTotalMes
        ];
        
        $this->responseJson([
            'success' => true,
            'estatisticas' => $estatisticas
        ]);
    }

    public function graficoNF(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar gráficos.'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $dados = $notaFiscal->getEstatisticasPorStatus();
        
        $labels = [];
        $values = [];
        
        foreach ($dados as $status => $count) {
            $labels[] = ucfirst($status);
            $values[] = $count;
        }
        
        $this->responseJson([
            'success' => true,
            'labels' => $labels,
            'values' => $values
        ]);
    }

    public function graficoConferencias(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar gráficos.'
            ]);
            return;
        }
        
        $conferencia = new ConferenciaProduto();
        $dados = $conferencia->getEstatisticasPorQualidade();
        
        $labels = [];
        $values = [];
        
        foreach ($dados as $qualidade => $count) {
            $labels[] = ucfirst($qualidade);
            $values[] = $count;
        }
        
        $this->responseJson([
            'success' => true,
            'labels' => $labels,
            'values' => $values
        ]);
    }

    public function graficoMovimentacoes(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar gráficos.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        $dados = $movimentacao->getEstatisticasPorTipo();
        
        $labels = [];
        $put_away = [];
        $transferencia = [];
        $reposicao = [];
        
        foreach ($dados as $data => $tipos) {
            $labels[] = $data;
            $put_away[] = $tipos['put_away'] ?? 0;
            $transferencia[] = $tipos['transferencia'] ?? 0;
            $reposicao[] = $tipos['reposicao'] ?? 0;
        }
        
        $this->responseJson([
            'success' => true,
            'labels' => $labels,
            'put_away' => $put_away,
            'transferencia' => $transferencia,
            'reposicao' => $reposicao
        ]);
    }

    public function nfRecentes(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar dados.'
            ]);
            return;
        }
        
        $notaFiscal = new NotaFiscal();
        $notasFiscais = $notaFiscal->getRecentes(5);
        
        $this->responseJson([
            'success' => true,
            'notas_fiscais' => $notasFiscais
        ]);
    }

    public function conferenciasRecentes(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar dados.'
            ]);
            return;
        }
        
        $conferencia = new ConferenciaProduto();
        $conferencias = $conferencia->getRecentes(5);
        
        $this->responseJson([
            'success' => true,
            'conferencias' => $conferencias
        ]);
    }

    public function movimentacoesRecentes(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('recebimento', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar dados.'
            ]);
            return;
        }
        
        $movimentacao = new MovimentacaoInterna();
        $movimentacoes = $movimentacao->getRecentes(5);
        
        $this->responseJson([
            'success' => true,
            'movimentacoes' => $movimentacoes
        ]);
    }
} 