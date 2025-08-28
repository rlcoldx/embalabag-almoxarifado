-- Migration: 015_create_itens_pedido_table.sql
-- Descrição: Criação da tabela de itens dos pedidos

CREATE TABLE `itens_pedido` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `quantidade_solicitada` INT(11) NOT NULL,
  `quantidade_separada` INT(11) NULL DEFAULT NULL,
  `quantidade_embalada` INT(11) NULL DEFAULT NULL,
  `valor_unitario` DECIMAL(10,2) NULL DEFAULT NULL,
  `valor_total` DECIMAL(10,2) NULL DEFAULT NULL,
  `armazenagem_id` INT(11) NULL DEFAULT NULL,
  `status` ENUM('pendente', 'separado', 'embalado', 'finalizado') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_pedido_id` (`pedido_id`) USING BTREE,
  INDEX `idx_produto_id` (`produto_id`) USING BTREE,
  INDEX `idx_armazenagem_id` (`armazenagem_id`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE,
  CONSTRAINT `fk_itens_pedido_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_itens_pedido_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_itens_pedido_armazenagem` FOREIGN KEY (`armazenagem_id`) REFERENCES `armazenagens` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;