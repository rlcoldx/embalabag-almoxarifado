-- Remover todos os triggers existentes
DROP TRIGGER IF EXISTS trigger_estoque_insert;
DROP TRIGGER IF EXISTS trigger_estoque_update;
DROP TRIGGER IF EXISTS trigger_estoque_delete;
DROP TRIGGER IF EXISTS trigger_estoque_produto_variation_insert;
DROP TRIGGER IF EXISTS trigger_estoque_produto_variation_update;
DROP TRIGGER IF EXISTS trigger_estoque_produto_variation_delete;

DELIMITER //

CREATE TRIGGER trigger_estoque_insert
AFTER INSERT ON estoque
FOR EACH ROW
BEGIN
    UPDATE armazenagens 
    SET capacidade_atual = (
        SELECT COALESCE(SUM(quantidade), 0)
        FROM estoque 
        WHERE armazenagem_id = NEW.armazenagem_id
          AND status = 'ativo'
    )
    WHERE id = NEW.armazenagem_id;
    
    UPDATE produtos_variations 
    SET estoque = (
        SELECT COALESCE(SUM(e.quantidade), 0)
        FROM estoque e 
        WHERE e.id_produto = NEW.id_produto 
          AND e.variacao_id = NEW.variacao_id
          AND e.status = 'ativo'
    )
    WHERE id_produto = NEW.id_produto 
      AND id = NEW.variacao_id;
END//

CREATE TRIGGER trigger_estoque_update
AFTER UPDATE ON estoque
FOR EACH ROW
BEGIN
    -- Se mudou de armazenagem, atualizar a armazenagem antiga
    IF OLD.armazenagem_id != NEW.armazenagem_id THEN
        UPDATE armazenagens 
        SET capacidade_atual = (
            SELECT COALESCE(SUM(quantidade), 0)
            FROM estoque 
            WHERE armazenagem_id = OLD.armazenagem_id
              AND status = 'ativo'
        )
        WHERE id = OLD.armazenagem_id;
    END IF;
    
    -- Atualizar a armazenagem nova (ou atual se não mudou)
    UPDATE armazenagens 
    SET capacidade_atual = (
        SELECT COALESCE(SUM(quantidade), 0)
        FROM estoque 
        WHERE armazenagem_id = NEW.armazenagem_id
          AND status = 'ativo'
    )
    WHERE id = NEW.armazenagem_id;
    
    -- Se mudou de produto ou variação, atualizar o estoque da variação antiga
    IF OLD.id_produto != NEW.id_produto OR OLD.variacao_id != NEW.variacao_id THEN
        UPDATE produtos_variations 
        SET estoque = (
            SELECT COALESCE(SUM(e.quantidade), 0)
            FROM estoque e 
            WHERE e.id_produto = OLD.id_produto 
              AND e.variacao_id = OLD.variacao_id
              AND e.status = 'ativo'
        )
        WHERE id_produto = OLD.id_produto 
          AND id = OLD.variacao_id;
    END IF;
    
    -- Atualizar estoque da variação nova (ou atual se não mudou)
    UPDATE produtos_variations 
    SET estoque = (
        SELECT COALESCE(SUM(e.quantidade), 0)
        FROM estoque e 
        WHERE e.id_produto = NEW.id_produto 
          AND e.variacao_id = NEW.variacao_id
          AND e.status = 'ativo'
    )
    WHERE id_produto = NEW.id_produto 
      AND id = NEW.variacao_id;
END//

CREATE TRIGGER trigger_estoque_delete
AFTER DELETE ON estoque
FOR EACH ROW
BEGIN
    -- Atualizar capacidade da armazenagem
    UPDATE armazenagens 
    SET capacidade_atual = (
        SELECT COALESCE(SUM(quantidade), 0)
        FROM estoque 
        WHERE armazenagem_id = OLD.armazenagem_id
          AND status = 'ativo'
    )
    WHERE id = OLD.armazenagem_id;
    
    -- Atualizar estoque total na tabela produtos_variations
    UPDATE produtos_variations 
    SET estoque = (
        SELECT COALESCE(SUM(e.quantidade), 0)
        FROM estoque e 
        WHERE e.id_produto = OLD.id_produto 
          AND e.variacao_id = OLD.variacao_id
          AND e.status = 'ativo'
    )
    WHERE id_produto = OLD.id_produto 
      AND id = OLD.variacao_id;
END//

DELIMITER ;

-- Atualizar capacidade_atual para todas as armazenagens baseado no estoque ativo
UPDATE armazenagens a 
SET capacidade_atual = (
    SELECT COALESCE(SUM(quantidade), 0)
    FROM estoque 
    WHERE armazenagem_id = a.id
      AND status = 'ativo'
);

-- Atualizar estoque para todas as variações baseado no estoque ativo
UPDATE produtos_variations pv 
SET estoque = (
    SELECT COALESCE(SUM(e.quantidade), 0)
    FROM estoque e 
    WHERE e.id_produto = pv.id_produto 
      AND e.variacao_id = pv.id
      AND e.status = 'ativo'
);
