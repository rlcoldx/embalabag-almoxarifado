-- Migration: 045_fix_triggers_exclude_deleted_products.sql
-- Descrição: Atualizar triggers para excluir estoque inativo do cálculo de capacidade_atual
-- Quando um produto é deletado, seu status na tabela estoque é marcado como 'inativo'

-- Primeiro, marcar como inativo todos os estoques de produtos deletados
UPDATE estoque e
INNER JOIN produtos p ON CAST(e.id_produto AS UNSIGNED) = p.id
SET e.status = 'inativo'
WHERE p.status = 'Deletado';

-- Drop triggers existentes
DROP TRIGGER IF EXISTS trigger_estoque_insert;
DROP TRIGGER IF EXISTS trigger_estoque_update;
DROP TRIGGER IF EXISTS trigger_estoque_delete;

-- Trigger para INSERT na tabela estoque
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
END//

-- Trigger para UPDATE na tabela estoque
CREATE TRIGGER trigger_estoque_update
AFTER UPDATE ON estoque
FOR EACH ROW
BEGIN
    -- Atualizar armazenagem antiga se mudou de armazenagem
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
    
    -- Atualizar armazenagem nova
    UPDATE armazenagens 
    SET capacidade_atual = (
        SELECT COALESCE(SUM(quantidade), 0)
        FROM estoque 
        WHERE armazenagem_id = NEW.armazenagem_id
          AND status = 'ativo'
    )
    WHERE id = NEW.armazenagem_id;
END//

-- Trigger para DELETE na tabela estoque
CREATE TRIGGER trigger_estoque_delete
AFTER DELETE ON estoque
FOR EACH ROW
BEGIN
    UPDATE armazenagens 
    SET capacidade_atual = (
        SELECT COALESCE(SUM(quantidade), 0)
        FROM estoque 
        WHERE armazenagem_id = OLD.armazenagem_id
          AND status = 'ativo'
    )
    WHERE id = OLD.armazenagem_id;
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

