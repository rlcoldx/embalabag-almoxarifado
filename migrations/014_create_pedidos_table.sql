-- Migration: 014_create_pedidos_table.sql
-- Descrição: Criação da tabela de pedidos

CREATE TABLE `pedidos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `cliente_id` INT(11) NULL DEFAULT NULL,
  `cliente_nome` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `cliente_email` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `cliente_telefone` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `data_pedido` DATE NOT NULL,
  `data_previsao_entrega` DATE NULL DEFAULT NULL,
  `data_entrega` DATE NULL DEFAULT NULL,
  `status` ENUM('pendente', 'aprovado', 'em_separacao', 'separado', 'embalado', 'enviado', 'entregue', 'cancelado') NOT NULL DEFAULT 'pendente' COLLATE 'utf8mb4_unicode_ci',
  `prioridade` ENUM('baixa', 'normal', 'alta', 'urgente') NOT NULL DEFAULT 'normal' COLLATE 'utf8mb4_unicode_ci',
  `valor_total` DECIMAL(10,2) NULL DEFAULT NULL,
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usuario_criacao` INT(11) NOT NULL,
  `usuario_aprovacao` INT(11) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero` (`numero`) USING BTREE,
  INDEX `idx_cliente_id` (`cliente_id`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE,
  INDEX `idx_prioridade` (`prioridade`) USING BTREE,
  INDEX `idx_data_pedido` (`data_pedido`) USING BTREE,
  INDEX `idx_usuario_criacao` (`usuario_criacao`) USING BTREE,
  INDEX `idx_usuario_aprovacao` (`usuario_aprovacao`) USING BTREE,
  CONSTRAINT `fk_pedidos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pedidos_usuario_criacao` FOREIGN KEY (`usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_pedidos_usuario_aprovacao` FOREIGN KEY (`usuario_aprovacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;