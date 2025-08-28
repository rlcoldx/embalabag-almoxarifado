-- Migration: 029_create_movimentacoes_internas.sql
-- Descrição: Criação da tabela de movimentações internas (put-away)

CREATE TABLE `movimentacoes_internas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_nf_id` INT(11) NOT NULL,
  `armazenagem_origem_id` INT(11) NULL DEFAULT NULL,
  `armazenagem_destino_id` INT(11) NOT NULL,
  `usuario_movimentacao` INT(11) NOT NULL,
  `tipo_movimentacao` ENUM('put_away', 'transferencia', 'reposicao', 'ajuste') NOT NULL DEFAULT 'put_away' COLLATE 'utf8mb4_unicode_ci',
  `quantidade_movimentada` INT(11) NOT NULL,
  `motivo` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `data_movimentacao` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `status` ENUM('pendente', 'em_andamento', 'concluida', 'cancelada') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_unicode_ci',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_item_nf_id` (`item_nf_id`) USING BTREE,
  INDEX `idx_armazenagem_origem` (`armazenagem_origem_id`) USING BTREE,
  INDEX `idx_armazenagem_destino` (`armazenagem_destino_id`) USING BTREE,
  INDEX `idx_usuario_movimentacao` (`usuario_movimentacao`) USING BTREE,
  INDEX `idx_tipo_movimentacao` (`tipo_movimentacao`) USING BTREE,
  INDEX `idx_data_movimentacao` (`data_movimentacao`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE,
  CONSTRAINT `fk_movimentacao_item_nf` FOREIGN KEY (`item_nf_id`) REFERENCES `pedidos_nf` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_movimentacao_armazenagem_origem` FOREIGN KEY (`armazenagem_origem_id`) REFERENCES `armazenagens` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_movimentacao_armazenagem_destino` FOREIGN KEY (`armazenagem_destino_id`) REFERENCES `armazenagens` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_movimentacao_usuario` FOREIGN KEY (`usuario_movimentacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB; 