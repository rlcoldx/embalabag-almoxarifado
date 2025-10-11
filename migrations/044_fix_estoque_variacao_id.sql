-- Migration: 044_fix_estoque_variacao_id.sql
-- Descrição: Corrige variacao_id na tabela estoque que podem estar com valores incorretos
-- Data: 2025-01-10

-- Esta migration corrige registros na tabela estoque onde o variacao_id
-- não corresponde a uma variação real do produto

-- Primeiro, vamos identificar registros problemáticos e tentar corrigi-los
-- Se houver apenas uma variação para o produto, associa automaticamente
UPDATE estoque e
INNER JOIN (
    SELECT 
        pv.id_produto,
        MIN(pv.id) as primeira_variacao_id,
        COUNT(*) as total_variacoes
    FROM produtos_variations pv
    GROUP BY pv.id_produto
    HAVING total_variacoes = 1
) as pv_info ON CAST(e.id_produto AS UNSIGNED) = pv_info.id_produto
LEFT JOIN produtos_variations pv ON pv.id = e.variacao_id
WHERE pv.id IS NULL  -- Variação não existe
SET e.variacao_id = pv_info.primeira_variacao_id;

-- Para produtos com múltiplas variações onde a variacao_id está incorreta,
-- vamos apenas registrar um log (não podemos adivinhar qual é a variação correta)
-- O administrador terá que corrigir manualmente ou refazer a entrada

-- Verificar registros que ainda precisam de correção manual
SELECT 
    e.id,
    e.armazenagem_id,
    e.id_produto,
    e.variacao_id,
    e.quantidade,
    p.nome as produto_nome,
    p.SKU as produto_sku,
    'NECESSITA CORREÇÃO MANUAL' as status
FROM estoque e
INNER JOIN produtos p ON CAST(e.id_produto AS UNSIGNED) = p.id
LEFT JOIN produtos_variations pv ON pv.id = e.variacao_id
WHERE pv.id IS NULL;

-- Nota: Execute esta query acima separadamente para ver se há registros
-- que precisam de correção manual

