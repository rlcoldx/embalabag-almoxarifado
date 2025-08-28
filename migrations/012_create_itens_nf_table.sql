-- Migration: 012_create_itens_nf_table.sql
-- Descrição: Criação da tabela de itens das notas fiscais

CREATE TABLE `pedidos_nf` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nota_fiscal_id` INT(11) NOT NULL,
  `produto_id` INT(11) NULL DEFAULT NULL,
  `codigo_produto` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `descricao_produto` VARCHAR(500) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `quantidade` INT(11) NOT NULL,
  `quantidade_conferida` INT(11) NULL DEFAULT NULL,
  `unidade_medida` VARCHAR(20) NOT NULL DEFAULT 'UN' COLLATE 'utf8mb4_unicode_ci',
  `valor_unitario` DECIMAL(10,2) NULL DEFAULT NULL,
  `valor_total` DECIMAL(10,2) NULL DEFAULT NULL,
  `armazenagem_id` INT(11) NULL DEFAULT NULL,
  `status` ENUM('pendente', 'conferido', 'alocado', 'finalizado') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_nota_fiscal_id` (`nota_fiscal_id`) USING BTREE,
  INDEX `idx_produto_id` (`produto_id`) USING BTREE,
  INDEX `idx_armazenagem_id` (`armazenagem_id`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE,
  CONSTRAINT `fk_pedidos_nf_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `notas_fiscais` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_pedidos_nf_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pedidos_nf_armazenagem` FOREIGN KEY (`armazenagem_id`) REFERENCES `armazenagens` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;