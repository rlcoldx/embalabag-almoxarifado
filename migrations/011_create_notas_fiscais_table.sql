-- Migration: 011_create_notas_fiscais_table.sql
-- Descrição: Criação da tabela de notas fiscais para recebimento

CREATE TABLE `notas_fiscais` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `serie` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `fornecedor` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `cnpj_fornecedor` VARCHAR(18) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `data_emissao` DATE NOT NULL,
  `data_recebimento` DATE NULL DEFAULT NULL,
  `valor_total` DECIMAL(10,2) NULL DEFAULT NULL,
  `status` ENUM('pendente', 'recebida', 'conferida', 'finalizada', 'cancelada') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usuario_recebimento` INT(11) NULL DEFAULT NULL,
  `usuario_conferencia` INT(11) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero_serie` (`numero`, `serie`) USING BTREE,
  INDEX `idx_fornecedor` (`fornecedor`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE,
  INDEX `idx_data_recebimento` (`data_recebimento`) USING BTREE,
  INDEX `fk_nf_usuario_recebimento` (`usuario_recebimento`) USING BTREE,
  INDEX `fk_nf_usuario_conferencia` (`usuario_conferencia`) USING BTREE,
  CONSTRAINT `fk_nf_usuario_recebimento` FOREIGN KEY (`usuario_recebimento`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_nf_usuario_conferencia` FOREIGN KEY (`usuario_conferencia`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;