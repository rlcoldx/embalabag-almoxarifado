<?php

namespace Agencia\Close\Services\Sige;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Pedidos\Pedidos;
use Agencia\Close\Models\Pedidos\PedidosItens;

class SigeService
{
    private const DEFAULT_URL = 'https://api.sigecloud.com.br/request';

    public static function isConfigured(): bool
    {
        return defined('SIGE_AUTHORIZATIONTOKEN') && trim((string)SIGE_AUTHORIZATIONTOKEN) !== ''
            && defined('SIGE_USER') && trim((string)SIGE_USER) !== ''
            && defined('SIGE_APP') && trim((string)SIGE_APP) !== '';
    }

    public function buscarPedido(string $codigo): array
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return ['success' => false, 'message' => 'Informe o código do pedido SIGE.'];
        }

        $local = $this->buscarLocal($codigo);
        if ($local) {
            return [
                'success' => true,
                'origem' => 'local',
                'pedido' => $local,
                'sige' => null,
            ];
        }

        if (!self::isConfigured()) {
            return [
                'success' => false,
                'message' => 'Pedido não encontrado no almoxarifado. Configure SIGE_AUTHORIZATIONTOKEN, SIGE_USER e SIGE_APP no config.php.',
            ];
        }

        $remoto = $this->consultarApi($codigo);
        if (!$remoto['success']) {
            return $remoto;
        }

        return [
            'success' => true,
            'origem' => 'sige',
            'pedido' => null,
            'sige' => $remoto['data'],
        ];
    }

    public function importarPedido(string $codigo): array
    {
        $consulta = $this->buscarPedido($codigo);
        if (!$consulta['success']) {
            return $consulta;
        }

        if (!empty($consulta['pedido'])) {
            return [
                'success' => true,
                'message' => 'Pedido já existe no almoxarifado.',
                'pedido' => $consulta['pedido'],
            ];
        }

        $dados = $this->normalizarPedido($consulta['sige'] ?? [], $codigo);
        $pedidos = new Pedidos();
        $id = $pedidos->createPedido([
            'codigo' => $dados['codigo'],
            'codigoSige' => $dados['codigo'],
            'nome_cliente' => $dados['cliente'],
            'email_cliente' => $dados['email'],
            'cpf_cliente' => $dados['documento'],
            'base_destino' => $dados['deposito'],
            'previsao_entrega' => $dados['previsao'],
            'valor_total' => $dados['valor'],
            'status_pedido' => '2',
            'status_expedicao' => 'aprovado',
            'status_sige' => $dados['status'],
            'observacoes' => $dados['observacoes'],
        ]);

        if (!$id) {
            return ['success' => false, 'message' => 'Não foi possível gravar o pedido importado do SIGE.'];
        }

        $this->importarItens((int)$id, $consulta['sige']['Items'] ?? $consulta['sige']['items'] ?? []);

        $local = $this->buscarLocal((string)$id) ?: $this->buscarLocal($codigo);
        return [
            'success' => true,
            'message' => 'Pedido importado do SIGE e marcado como aprovado.',
            'pedido' => $local,
        ];
    }

    public function testarConexao(): array
    {
        if (!self::isConfigured()) {
            return ['success' => false, 'message' => 'SIGE_AUTHORIZATIONTOKEN, SIGE_USER e SIGE_APP não estão preenchidos.'];
        }

        $resultado = $this->request($this->baseUrl() . '/pedidos/pesquisar?pageSize=1&skip=0');
        if ($resultado['http'] >= 200 && $resultado['http'] < 300) {
            return ['success' => true, 'message' => 'Conexão com o SIGE Cloud autenticada.', 'data' => $resultado['body']];
        }

        return [
            'success' => false,
            'message' => 'O SIGE Cloud recusou a autenticação (HTTP ' . $resultado['http'] . '). Confira token, usuário e App.',
            'data' => $resultado['body'],
        ];
    }

    private function buscarLocal(string $codigo): ?array
    {
        $read = new Read();
        if (ctype_digit($codigo)) {
            $read->FullRead(
                "SELECT * FROM pedidos WHERE id = :id OR codigo = :codigo OR codigoSige = :sige LIMIT 1",
                "id={$codigo}&codigo={$codigo}&sige={$codigo}"
            );
        } else {
            $read->FullRead(
                "SELECT * FROM pedidos WHERE codigo = :codigo OR codigoSige = :sige OR etiqueta_interna = :etiq OR etiqueta_cia_aerea = :cia LIMIT 1",
                "codigo={$codigo}&sige={$codigo}&etiq={$codigo}&cia={$codigo}"
            );
        }

        return $read->getResult()[0] ?? null;
    }

    private function consultarApi(string $codigo): array
    {
        $url = $this->baseUrl() . '/pedidos/pesquisar?' . http_build_query([
            'codigo' => $codigo,
            'pageSize' => 10,
            'skip' => 0,
        ]);
        $resultado = $this->request($url);
        if ($resultado['http'] < 200 || $resultado['http'] >= 300) {
            return [
                'success' => false,
                'message' => 'SIGE Cloud não encontrou o pedido ou recusou o token (HTTP ' . $resultado['http'] . ').',
            ];
        }

        $pedido = $this->primeiroPedido($resultado['body'], $codigo);
        if (!$pedido) {
            return ['success' => false, 'message' => 'Nenhum pedido com esse código no SIGE Cloud.'];
        }

        return ['success' => true, 'data' => $pedido];
    }

    private function primeiroPedido($body, string $codigo): ?array
    {
        if (!is_array($body)) {
            return null;
        }

        $lista = $body;
        if (isset($body['data']) && is_array($body['data'])) {
            $lista = $body['data'];
        }

        if (isset($lista['Codigo']) || isset($lista['codigo'])) {
            return $lista;
        }

        foreach ($lista as $item) {
            if (!is_array($item)) {
                continue;
            }
            $codigoItem = (string)($item['Codigo'] ?? $item['codigo'] ?? '');
            if ($codigoItem === '' || $codigoItem === $codigo) {
                return $item;
            }
        }

        return is_array($lista[0] ?? null) ? $lista[0] : null;
    }

    private function normalizarPedido(array $dados, string $codigo): array
    {
        $previsao = $dados['PrevisaoEntrega'] ?? $dados['previsaoEntrega'] ?? '';
        if (is_string($previsao) && strpos($previsao, '0001-01-01') === 0) {
            $previsao = '';
        }

        return [
            'codigo' => (string)($dados['Codigo'] ?? $dados['codigo'] ?? $codigo),
            'cliente' => (string)($dados['Cliente'] ?? $dados['cliente'] ?? 'SIGE'),
            'email' => (string)($dados['ClienteEmail'] ?? ''),
            'documento' => (string)($dados['ClienteCNPJ'] ?? ''),
            'deposito' => (string)($dados['Deposito'] ?? $dados['deposito'] ?? ''),
            'previsao' => is_string($previsao) ? substr($previsao, 0, 10) : '',
            'valor' => (string)($dados['ValorFinal'] ?? $dados['valorFinal'] ?? '0.00'),
            'status' => (string)($dados['StatusSistema'] ?? $dados['Status'] ?? 'importado'),
            'observacoes' => trim((string)($dados['Descricao'] ?? 'Importado do SIGE Cloud')),
        ];
    }

    private function importarItens(int $pedidoId, array $itens): void
    {
        if ($itens === []) {
            return;
        }

        $itensPedido = new PedidosItens();
        foreach ($itens as $item) {
            if (!is_array($item)) {
                continue;
            }
            $sku = (string)($item['Codigo'] ?? $item['codigo'] ?? '');
            $produtoId = $this->produtoIdPorSku($sku);
            if (!$produtoId) {
                continue;
            }
            $qty = (int)($item['Quantidade'] ?? $item['quantidade'] ?? 1);
            $valor = (float)($item['ValorUnitario'] ?? $item['valorUnitario'] ?? 0);
            $itensPedido->createItem([
                'id_pedido' => $pedidoId,
                'id_produto' => $produtoId,
                'qty' => $qty,
                'valor_unidade' => number_format($valor, 2, '.', ''),
                'valor_total' => number_format($valor * $qty, 2, '.', ''),
            ]);
        }
    }

    private function produtoIdPorSku(string $sku): ?int
    {
        $sku = trim($sku);
        if ($sku === '') {
            return null;
        }

        $read = new Read();
        $read->FullRead("SELECT id FROM produtos WHERE SKU = :sku AND status <> 'Deletado' LIMIT 1", "sku={$sku}");
        $row = $read->getResult()[0] ?? null;
        return $row ? (int)$row['id'] : null;
    }

    private function baseUrl(): string
    {
        if (defined('SIGE_API_URL') && trim((string)SIGE_API_URL) !== '') {
            return rtrim((string)SIGE_API_URL, '/');
        }
        return self::DEFAULT_URL;
    }

    private function request(string $url): array
    {
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization-Token: ' . SIGE_AUTHORIZATIONTOKEN,
            'User: ' . SIGE_USER,
            'App: ' . SIGE_APP,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $raw = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode((string)$raw, true);
        return [
            'http' => $http,
            'body' => $decoded !== null ? $decoded : ['raw' => $raw, 'error' => $error],
        ];
    }
}
