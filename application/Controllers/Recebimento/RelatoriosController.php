<?php

namespace Agencia\Close\Controllers\Recebimento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\NotaFiscal\NotaFiscal;
use Agencia\Close\Models\Conferencia\ConferenciaRecebimento;
use Agencia\Close\Models\Movimentacao\MovimentacaoInterna;
use Agencia\Close\Models\Transferencias\Transferencias;
use Agencia\Close\Helpers\User\PermissionHelper;
use Agencia\Close\Helpers\User\ResponsavelHelper;

class RelatoriosController extends Controller
{
    public function index(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (
            !$permissionHelper->userHasPermission('relatorios', 'visualizar')
            && !$permissionHelper->userHasPermission('relatorio', 'visualizar')
        ) {
            echo 'Sem permissão para acessar este módulo.';
            return;
        }
        
        $this->render('pages/recebimento/relatorios/index.twig', [
            'menu' => 'recebimento_relatorios',
            'usuarios_responsaveis' => ResponsavelHelper::listar()
        ]);
    }

    public function recebimento(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (
            !$permissionHelper->userHasPermission('relatorios', 'visualizar')
            && !$permissionHelper->userHasPermission('relatorio', 'visualizar')
        ) {
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
        if (
            !$permissionHelper->userHasPermission('relatorios', 'visualizar')
            && !$permissionHelper->userHasPermission('relatorio', 'visualizar')
        ) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar relatórios.'
            ]);
            return;
        }
        
        $filtros = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($filtros) || $filtros === []) {
            $filtros = $_POST ?: $_GET;
        }

        $conferencia = new ConferenciaRecebimento();
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
        if (
            !$permissionHelper->userHasPermission('relatorios', 'visualizar')
            && !$permissionHelper->userHasPermission('relatorio', 'visualizar')
        ) {
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
        if (
            !$permissionHelper->userHasPermission('relatorios', 'visualizar')
            && !$permissionHelper->userHasPermission('relatorio', 'visualizar')
        ) {
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

    public function transferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->podeVisualizarRelatorio()) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para acessar relatórios.'
            ]);
            return;
        }

        $filtros = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($filtros)) {
            $filtros = $_GET;
        }

        $transferencias = new Transferencias();
        $this->responseJson([
            'success' => true,
            'relatorio' => $transferencias->gerarRelatorioTransferencias($filtros)
        ]);
    }

    public function exportarRecebimento(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$this->podeExportarRelatorio()) {
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
        if (!$this->podeExportarRelatorio()) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para exportar relatórios.'
            ]);
            return;
        }
        
        $filtros = $_GET;
        
        $conferencia = new ConferenciaRecebimento();
        $dados = $conferencia->gerarRelatorioConferencia($filtros);
        
        $this->exportarExcel($dados, 'relatorio_conferencia');
    }

    public function exportarMovimentacao(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$this->podeExportarRelatorio()) {
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
        if (!$this->podeExportarRelatorio()) {
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

    public function exportarTransferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->podeExportarRelatorio()) {
            $this->responseJson([
                'success' => false,
                'message' => 'Sem permissão para exportar relatórios.'
            ]);
            return;
        }

        $transferencias = new Transferencias();
        $this->exportarExcel($transferencias->gerarRelatorioTransferencias($_GET), 'relatorio_transferencias');
    }

    private function exportarExcel($dados, $nomeArquivo)
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');

        $headers = $dados['headers'] ?? [];
        $rows = $dados['data'] ?? [];

        if ($headers === [] && !empty($dados['dados']) && is_array($dados['dados'])) {
            $headers = array_keys($dados['dados'][0]);
            $rows = $dados['dados'];
        }

        echo '<table border="1">';

        if ($headers !== []) {
            echo '<tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars((string)$header) . '</th>';
            }
            echo '</tr>';
        }

        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>';
        exit;
    }

    private function podeVisualizarRelatorio(): bool
    {
        $permissionHelper = new PermissionHelper();
        return $permissionHelper->userHasPermission('relatorios', 'visualizar')
            || $permissionHelper->userHasPermission('relatorio', 'visualizar');
    }

    private function podeExportarRelatorio(): bool
    {
        $permissionHelper = new PermissionHelper();
        return $permissionHelper->userHasPermission('relatorios', 'exportar')
            || $permissionHelper->userHasPermission('relatorio', 'exportar')
            || $this->podeVisualizarRelatorio();
    }

    private function podeImprimirRelatorio(): bool
    {
        $permissionHelper = new PermissionHelper();
        return $permissionHelper->userHasPermission('relatorios', 'imprimir')
            || $permissionHelper->userHasPermission('relatorio', 'imprimir')
            || $this->podeVisualizarRelatorio();
    }

    public function imprimirRecebimento(array $params)
    {
        $this->checkSession();
        $this->setParams($params);
        
        $permissionHelper = new PermissionHelper();
        if (!$this->podeImprimirRelatorio()) {
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
        if (!$this->podeImprimirRelatorio()) {
            echo 'Sem permissão para imprimir relatórios.';
            return;
        }
        
        $filtros = $_GET;
        
        $conferencia = new ConferenciaRecebimento();
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
        if (!$this->podeImprimirRelatorio()) {
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
        if (!$this->podeImprimirRelatorio()) {
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

    public function imprimirTransferencia(array $params)
    {
        $this->checkSession();
        $this->setParams($params);

        if (!$this->podeImprimirRelatorio()) {
            echo 'Sem permissão para imprimir relatórios.';
            return;
        }

        $transferencias = new Transferencias();
        $this->render('pages/recebimento/relatorios/print/transferencia.twig', [
            'dados' => $transferencias->gerarRelatorioTransferencias($_GET),
            'filtros' => $_GET
        ]);
    }
} 