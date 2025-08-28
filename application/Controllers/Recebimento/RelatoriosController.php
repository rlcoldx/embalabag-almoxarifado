<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Conferencia\ConferenciaProduto;
use Agencia\Close\Models\Movimentacao\MovimentacaoInterna;
use Agencia\Close\Helpers\User\PermissionHelper;

class RelatoriosController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'visualizar')) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $this->render('pages/recebimento/relatorios/index.twig', [
            'menu' => 'recebimento_relatorios'
        ]);
    }

    public function recebimento(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar relatórios.'
            ]);
            return;
        }
        
        $filtros = json_decode(file_get_contents('php://input'), true);
        
        $notaFiscal = new NotaFiscal();
        $relatorio = $notaFiscal->gerarRelatorioRecebimento($filtros);
        
        $this->responseJson([
            'success' => true,
            'relatorio' => $relatorio
        ]);
    }

    public function conferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar relatórios.'
            ]);
            return;
        }
        
        $filtros = json_decode(file_get_contents('php://input'), true);
        
        $conferencia = new ConferenciaProduto();
        $relatorio = $conferencia->gerarRelatorioConferencia($filtros);
        
        $this->responseJson([
            'success' => true,
            'relatorio' => $relatorio
        ]);
    }

    public function movimentacao(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar relatórios.'
            ]);
            return;
        }
        
        $filtros = json_decode(file_get_contents('php://input'), true);
        
        $movimentacao = new MovimentacaoInterna();
        $relatorio = $movimentacao->gerarRelatorioMovimentacao($filtros);
        
        $this->responseJson([
            'success' => true,
            'relatorio' => $relatorio
        ]);
    }

    public function etiquetas(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'visualizar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar relatórios.'
            ]);
            return;
        }
        
        $filtros = json_decode(file_get_contents('php://input'), true);
        
        $etiqueta = new \Agencia\Close\Models\Etiqueta\EtiquetaInterna();
        $relatorio = $etiqueta->gerarRelatorioEtiquetas($filtros);
        
        $this->responseJson([
            'success' => true,
            'relatorio' => $relatorio
        ]);
    }

    public function exportarRecebimento(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'exportar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para exportar relatórios.'
            ]);
            return;
        }
        
        $filtros = $_GET;
        
        $notaFiscal = new NotaFiscal();
        $dados = $notaFiscal->gerarRelatorioRecebimento($filtros);
        
        $this->exportarExcel($dados, 'relatorio_recebimento');
    }

    public function exportarConferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'exportar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para exportar relatórios.'
            ]);
            return;
        }
        
        $filtros = $_GET;
        
        $conferencia = new ConferenciaProduto();
        $dados = $conferencia->gerarRelatorioConferencia($filtros);
        
        $this->exportarExcel($dados, 'relatorio_conferencia');
    }

    public function exportarMovimentacao(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'exportar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para exportar relatórios.'
            ]);
            return;
        }
        
        $filtros = $_GET;
        
        $movimentacao = new MovimentacaoInterna();
        $dados = $movimentacao->gerarRelatorioMovimentacao($filtros);
        
        $this->exportarExcel($dados, 'relatorio_movimentacao');
    }

    public function exportarEtiquetas(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'exportar')) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para exportar relatórios.'
            ]);
            return;
        }
        
        $filtros = $_GET;
        
        $etiqueta = new \Agencia\Close\Models\Etiqueta\EtiquetaInterna();
        $dados = $etiqueta->gerarRelatorioEtiquetas($filtros);
        
        $this->exportarExcel($dados, 'relatorio_etiquetas');
    }

    private function exportarExcel($dados, $nomeArquivo)
    {
        // Configurar headers para download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Gerar conteúdo do Excel
        echo '<table border="1">';
        
        // Cabeçalho
        if (isset($dados['headers'])) {
            echo '<tr>';
            foreach ($dados['headers'] as $header) {
                echo '<th>' . $header . '</th>';
            }
            echo '</tr>';
        }
        
        // Dados
        if (isset($dados['data'])) {
            foreach ($dados['data'] as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . $cell . '</td>';
                }
                echo '</tr>';
            }
        }
        
        echo '</table>';
        exit;
    }

    public function imprimirRecebimento(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'imprimir')) {
            echo 'Sem permissão para imprimir relatórios.';
            return;
        }
        
        $filtros = $_GET;
        
        $notaFiscal = new NotaFiscal();
        $dados = $notaFiscal->gerarRelatorioRecebimento($filtros);
        
        $this->render('pages/recebimento/relatorios/print/recebimento.twig', [
            'dados' => $dados,
            'filtros' => $filtros
        ]);
    }

    public function imprimirConferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'imprimir')) {
            echo 'Sem permissão para imprimir relatórios.';
            return;
        }
        
        $filtros = $_GET;
        
        $conferencia = new ConferenciaProduto();
        $dados = $conferencia->gerarRelatorioConferencia($filtros);
        
        $this->render('pages/recebimento/relatorios/print/conferencia.twig', [
            'dados' => $dados,
            'filtros' => $filtros
        ]);
    }

    public function imprimirMovimentacao(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'imprimir')) {
            echo 'Sem permissão para imprimir relatórios.';
            return;
        }
        
        $filtros = $_GET;
        
        $movimentacao = new MovimentacaoInterna();
        $dados = $movimentacao->gerarRelatorioMovimentacao($filtros);
        
        $this->render('pages/recebimento/relatorios/print/movimentacao.twig', [
            'dados' => $dados,
            'filtros' => $filtros
        ]);
    }

    public function imprimirEtiquetas(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$permissionHelper->userHasPermission('relatorio', 'imprimir')) {
            echo 'Sem permissão para imprimir relatórios.';
            return;
        }
        
        $filtros = $_GET;
        
        $etiqueta = new \Agencia\Close\Models\Etiqueta\EtiquetaInterna();
        $dados = $etiqueta->gerarRelatorioEtiquetas($filtros);
        
        $this->render('pages/recebimento/relatorios/print/etiquetas.twig', [
            'dados' => $dados,
            'filtros' => $filtros
        ]);
    }
} 