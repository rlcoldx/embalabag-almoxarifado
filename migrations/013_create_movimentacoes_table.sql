-- Migration: 013_create_movimentacoes_table.sql
-- Descrição: Criação da tabela de movimentações de estoque

CREATE TABLE `movimentacoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `produto_id` INT(11) NOT NULL,
  `armazenagem_origem_id` INT(11) NULL DEFAULT NULL,
  `armazenagem_destino_id` INT(11) NULL DEFAULT NULL,
  `tipo` ENUM('entrada', 'saida', 'transferencia', 'ajuste') NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `quantidade` INT(11) NOT NULL,
  `quantidade_anterior` INT(11) NULL DEFAULT NULL,
  `quantidade_atual` INT(11) NULL DEFAULT NULL,
  `motivo` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `documento_referencia` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usuario_id` INT(11) NOT NULL,
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_produto_id` (`produto_id`) USING BTREE,
  INDEX `idx_armazenagem_origem` (`armazenagem_origem_id`) USING BTREE,
  INDEX `idx_armazenagem_destino` (`armazenagem_destino_id`) USING BTREE,
  INDEX `idx_tipo` (`tipo`) USING BTREE,
  INDEX `idx_usuario_id` (`usuario_id`) USING BTREE,
  INDEX `idx_created_at` (`created_at`) USING BTREE,
  CONSTRAINT `fk_movimentacoes_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_movimentacoes_armazenagem_origem` FOREIGN KEY (`armazenagem_origem_id`) REFERENCES `armazenagens` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_movimentacoes_armazenagem_destino` FOREIGN KEY (`armazenagem_destino_id`) REFERENCES `armazenagens` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_movimentacoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;