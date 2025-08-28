-- Migration: 041_update_armazenagens_capacidade_trigger.sql
-- Descrição: Criar triggers para atualizar automaticamente o campo capacidade_atual na tabela armazenagens

-- Drop triggers se existirem
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
    )
    WHERE id = NEW.armazenagem_id;
END//

-- Trigger para UPDATE na tabela estoque
CREATE TRIGGER trigger_estoque_update
AFTER UPDATE ON estoque
FOR EACH ROW
BEGIN
    UPDATE armazenagens 
    SET capacidade_atual = (
        SELECT COALESCE(SUM(quantidade), 0)
        FROM estoque 
        WHERE armazenagem_id = NEW.armazenagem_id
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
    )
    WHERE id = OLD.armazenagem_id;
END//

DELIMITER ;

-- Atualizar capacidade_atual para todas as armazenagens baseado no estoque atual
UPDATE armazenagens a 
SET capacidade_atual = (
    SELECT COALESCE(SUM(e.quantidade), 0)
    FROM estoque e 
    WHERE e.armazenagem_id = a.id
);
