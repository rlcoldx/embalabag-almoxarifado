-- Migration: 016_create_avarias_table.sql
-- Descrição: Criação da tabela de avarias para controle de qualidade

CREATE TABLE `avarias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `produto_id` INT(11) NULL DEFAULT NULL,
  `armazenagem_id` INT(11) NULL DEFAULT NULL,
  `tipo` ENUM('produto', 'armazenagem', 'embalagem') NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `severidade` ENUM('baixa', 'media', 'alta', 'critica') NOT NULL DEFAULT 'media' COLLATE 'utf8mb4_unicode_ci',
  `descricao` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `quantidade_afetada` INT(11) NULL DEFAULT NULL,
  `status` ENUM('aberta', 'em_analise', 'resolvida', 'fechada') NOT NULL DEFAULT 'aberta' COLLATE 'utf8mb4_unicode_ci',
  `data_ocorrencia` DATETIME NOT NULL,
  `data_resolucao` DATETIME NULL DEFAULT NULL,
  `usuario_reportou` INT(11) NOT NULL,
  `usuario_responsavel` INT(11) NULL DEFAULT NULL,
  `acao_corretiva` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_produto_id` (`produto_id`) USING BTREE,
  INDEX `idx_armazenagem_id` (`armazenagem_id`) USING BTREE,
  INDEX `idx_tipo` (`tipo`) USING BTREE,
  INDEX `idx_severidade` (`severidade`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE,
  INDEX `idx_data_ocorrencia` (`data_ocorrencia`) USING BTREE,
  INDEX `idx_usuario_reportou` (`usuario_reportou`) USING BTREE,
  INDEX `idx_usuario_responsavel` (`usuario_responsavel`) USING BTREE,
  CONSTRAINT `fk_avarias_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_avarias_armazenagem` FOREIGN KEY (`armazenagem_id`) REFERENCES `armazenagens` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_avarias_usuario_reportou` FOREIGN KEY (`usuario_reportou`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_avarias_usuario_responsavel` FOREIGN KEY (`usuario_responsavel`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;