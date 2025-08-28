-- Migration: 040_create_estoque_table.sql
-- Descrição: Criação da tabela central de estoque para controlar quantidade de produtos em cada armazenagem
-- Data: 2024-12-19

-- Criar tabela de estoque
CREATE TABLE IF NOT EXISTS `estoque` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `armazenagem_id` INT(11) NOT NULL,
  `id_produto` VARCHAR(255) NOT NULL,
  `variacao_id` BIGINT(255) NULL DEFAULT NULL,
  `quantidade` INT(11) NOT NULL DEFAULT 0,
  `quantidade_minima` INT(11) NULL DEFAULT 0,
  `quantidade_maxima` INT(11) NULL DEFAULT NULL,
  `status` ENUM('ativo', 'inativo', 'bloqueado') NOT NULL DEFAULT 'ativo',
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_armazenagem_id` (`armazenagem_id`),
  KEY `idx_id_produto` (`id_produto`),
  KEY `idx_variacao_id` (`variacao_id`),
  KEY `idx_status` (`status`),
  KEY `idx_quantidade` (`quantidade`),
  CONSTRAINT `fk_estoque_armazenagem` FOREIGN KEY (`armazenagem_id`) REFERENCES `armazenagens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de histórico de movimentações
CREATE TABLE IF NOT EXISTS `movimentacoes_historico` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tipo` ENUM('entrada', 'saida', 'movimentacao', 'ajuste') NOT NULL,
  `id_produto` VARCHAR(255) NOT NULL,
  `variacao_id` BIGINT(255) NULL DEFAULT NULL,
  `quantidade` INT(11) NOT NULL,
  `armazenagem_origem_id` INT(11) NULL DEFAULT NULL,
  `armazenagem_destino_id` INT(11) NULL DEFAULT NULL,
  `motivo` VARCHAR(255) NULL DEFAULT NULL,
  `documento_referencia` VARCHAR(100) NULL DEFAULT NULL,
  `observacoes` TEXT NULL DEFAULT NULL,
  `usuario_id` INT(11) NOT NULL,
  `data_movimentacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_id_produto` (`id_produto`),
  KEY `idx_variacao_id` (`variacao_id`),
  KEY `idx_armazenagem_origem` (`armazenagem_origem_id`),
  KEY `idx_armazenagem_destino` (`armazenagem_destino_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_data_movimentacao` (`data_movimentacao`),
  CONSTRAINT `fk_historico_armazenagem_origem` FOREIGN KEY (`armazenagem_origem_id`) REFERENCES `armazenagens` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_armazenagem_destino` FOREIGN KEY (`armazenagem_destino_id`) REFERENCES `armazenagens` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados iniciais de estoque baseado nos produtos existentes
-- (opcional - pode ser executado manualmente se necessário)
/*
INSERT INTO estoque (armazenagem_id, id_produto, variacao_id, quantidade, quantidade_minima)
SELECT 
    a.id as armazenagem_id,
    p.SKU,
    pv.id as variacao_id,
    COALESCE(pv.estoque, 0) as quantidade,
    COALESCE(pv.estoque_minimo, 1) as quantidade_minima
FROM armazenagens a
CROSS JOIN produtos p
LEFT JOIN produtos_variations pv ON p.id = pv.id_produto
WHERE p.status = 'Publicado' 
    AND a.status = 'ativo'
    AND p.SKU IS NOT NULL;
*/
