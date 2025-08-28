-- Migration: 042_update_produtos_variations_estoque_trigger.sql
-- Descrição: Criar triggers para atualizar automaticamente o campo estoque na tabela produtos_variations

-- Drop triggers se existirem
DROP TRIGGER IF EXISTS trigger_estoque_produto_variation_insert;
DROP TRIGGER IF EXISTS trigger_estoque_produto_variation_update;
DROP TRIGGER IF EXISTS trigger_estoque_produto_variation_delete;

-- Trigger para INSERT na tabela estoque
DELIMITER //
CREATE TRIGGER trigger_estoque_produto_variation_insert
AFTER INSERT ON estoque
FOR EACH ROW
BEGIN
    -- Atualizar estoque total na tabela produtos_variations
    UPDATE produtos_variations 
    SET estoque = (
        SELECT COALESCE(SUM(e.quantidade), 0)
        FROM estoque e 
        WHERE e.id_produto = NEW.id_produto AND e.variacao_id = NEW.variacao_id
    )
    WHERE id_produto = NEW.id_produto AND id = NEW.variacao_id;
END//

-- Trigger para UPDATE na tabela estoque
CREATE TRIGGER trigger_estoque_produto_variation_update
AFTER UPDATE ON estoque
FOR EACH ROW
BEGIN
    -- Atualizar estoque total na tabela produtos_variations
    UPDATE produtos_variations 
    SET estoque = (
        SELECT COALESCE(SUM(e.quantidade), 0)
        FROM estoque e 
        WHERE e.id_produto = NEW.id_produto AND e.variacao_id = NEW.variacao_id
    )
    WHERE id_produto = NEW.id_produto AND id = NEW.variacao_id;
END//

-- Trigger para DELETE na tabela estoque
CREATE TRIGGER trigger_estoque_produto_variation_delete
AFTER DELETE ON estoque
FOR EACH ROW
BEGIN
    -- Atualizar estoque total na tabela produtos_variations
    UPDATE produtos_variations 
    SET estoque = (
        SELECT COALESCE(SUM(e.quantidade), 0)
        FROM estoque e 
        WHERE e.id_produto = OLD.id_produto AND e.variacao_id = OLD.variacao_id
    )
    WHERE id_produto = OLD.id_produto AND id = OLD.variacao_id;
END//

DELIMITER ;

-- Atualizar estoque para todas as variações baseado no estoque atual
UPDATE produtos_variations pv 
SET estoque = (
    SELECT COALESCE(SUM(e.quantidade), 0)
    FROM estoque e 
    WHERE e.id_produto = pv.id_produto AND e.variacao_id = pv.id
);
