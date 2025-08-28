<?php

namespace Agencia\Close\Services\PrecoEmpresa;

use Agencia\Close\Models\Site\Produtos;
use Agencia\Close\Helpers\Result;

class PrecoEmpresaService
{
    private Produtos $produtosModel;
    private Result $result;

    public function __construct()
    {
        $this->produtosModel = new Produtos();
        $this->result = new Result();
    }

    /**
     * Obtém o preço específico da empresa para um produto
     */
    public function getPrecoEmpresa(int $id_produto, int $id_empresa): Result
    {
        $preco = $this->produtosModel->getProdutoPrecoEmpresa($id_produto, $id_empresa);
        
        if ($preco->getResult()) {
            $this->result->setError(false);
            $this->result->setResult($preco->getResult()[0]);
        } else {
            $this->result->setError(true);
            $this->result->setMessage('Preço não encontrado para esta empresa');
        }
        
        return $this->result;
    }

    /**
     * Obtém o preço baseado no tipo de usuário logado
     */
    public function getPrecoPorTipoUsuario(int $id_produto, array $session): float
    {
        // Se for admin (tipo 1) ou empresa (tipo 2), busca preço específico da empresa
        if (isset($session['embalabag_user_tipo']) && in_array($session['embalabag_user_tipo'], ['1', '2'])) {
            $id_empresa = $session['embalabag_user_empresa'] ?? $session['embalabag_user_id'];
            
            $precoEmpresa = $this->getPrecoEmpresa($id_produto, $id_empresa);
            
            if (!$precoEmpresa->getError()) {
                return floatval($precoEmpresa->getResult()['preco']);
            }
        }
        
        // Se não encontrar preço específico ou for cliente, retorna 0 (preço padrão será usado)
        return 0;
    }
} 