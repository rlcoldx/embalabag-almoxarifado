<?php

namespace Agencia\Close\Models\Produtos;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class ProdutoHistorico extends Model
{
    private const CAMPOS = [
        'SKU' => 'SKU',
        'nome' => 'Nome',
        'slug' => 'Slug',
        'texto' => 'Descrição',
        'observacoes' => 'Observações',
        'condicoes' => 'Condições',
        'marca' => 'Marca',
        'tamanho' => 'Tamanho',
        'material' => 'Material',
        'categoria' => 'Categoria',
        'valor' => 'Valor',
        'promocao' => 'Promoção',
        'promocao_tipo' => 'Tipo de promoção',
        'porcentagem' => 'Porcentagem',
        'tags' => 'Tags',
        'destaque' => 'Destaque',
        'status' => 'Status',
    ];

    public function getByProduto(int $produtoId): array
    {
        $read = new Read();
        $read->FullRead(
            "SELECT h.*, u.nome as usuario_nome
             FROM produtos_historico h
             LEFT JOIN usuarios u ON h.usuario_id = u.id
             WHERE h.produto_id = :produto_id
             ORDER BY h.created_at DESC, h.id DESC",
            "produto_id={$produtoId}"
        );

        $linhas = $read->getResult() ?: [];
        foreach ($linhas as &$linha) {
            $decodificado = json_decode($linha['alteracoes'] ?? '[]', true);
            $linha['alteracoes_lista'] = is_array($decodificado) ? $decodificado : [];
            $linha['acao_descricao'] = $this->descricaoAcao($linha['acao'] ?? '');
        }
        unset($linha);

        return $linhas;
    }

    public function registrarCriacao(int $produtoId, array $produto): void
    {
        $this->gravar($produtoId, 'criado', [
            [
                'campo' => 'nome',
                'rotulo' => 'Nome',
                'anterior' => '',
                'novo' => $this->resumir((string)($produto['nome'] ?? '')),
            ],
        ]);
    }

    public function registrarAtualizacao(int $produtoId, array $anterior, array $novo, bool $relacionadosAlterados = false): void
    {
        $alteracoes = $this->diff($anterior, $novo);
        if ($alteracoes === [] && $relacionadosAlterados) {
            $alteracoes[] = [
                'campo' => 'relacionados',
                'rotulo' => 'Variações / preços / categorias',
                'anterior' => '',
                'novo' => 'Atualizados',
            ];
        }

        if ($alteracoes === []) {
            return;
        }

        $this->gravar($produtoId, 'atualizado', $alteracoes);
    }

    public function registrarExclusao(int $produtoId, array $produto): void
    {
        $this->gravar($produtoId, 'excluido', [
            [
                'campo' => 'status',
                'rotulo' => 'Status',
                'anterior' => $this->resumir((string)($produto['status'] ?? '')),
                'novo' => 'Deletado',
            ],
        ]);
    }

    private function gravar(int $produtoId, string $acao, array $alteracoes): void
    {
        $usuarioId = (int)($_SESSION[BASE . 'user_id'] ?? 0);

        $create = new Create();
        $create->ExeCreate('produtos_historico', [
            'produto_id' => $produtoId,
            'acao' => $acao,
            'alteracoes' => json_encode($alteracoes, JSON_UNESCAPED_UNICODE),
            'usuario_id' => $usuarioId > 0 ? $usuarioId : null,
        ]);
    }

    private function diff(array $anterior, array $novo): array
    {
        $alteracoes = [];

        foreach (self::CAMPOS as $campo => $rotulo) {
            $valorAnterior = (string)($anterior[$campo] ?? '');
            $valorNovo = (string)($novo[$campo] ?? '');
            if ($valorAnterior === $valorNovo) {
                continue;
            }

            $alteracoes[] = [
                'campo' => $campo,
                'rotulo' => $rotulo,
                'anterior' => $this->resumir($valorAnterior),
                'novo' => $this->resumir($valorNovo),
            ];
        }

        return $alteracoes;
    }

    private function resumir(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return '(vazio)';
        }

        if (function_exists('mb_strlen') && mb_strlen($valor) > 180) {
            return mb_substr($valor, 0, 180) . '...';
        }

        if (strlen($valor) > 180) {
            return substr($valor, 0, 180) . '...';
        }

        return $valor;
    }

    private function descricaoAcao(string $acao): string
    {
        $mapa = [
            'criado' => 'Criado',
            'atualizado' => 'Atualizado',
            'excluido' => 'Excluído',
        ];

        return $mapa[$acao] ?? $acao;
    }
}
