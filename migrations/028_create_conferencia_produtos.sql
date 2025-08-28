-- Migration: 028_create_conferencia_produtos.sql
-- Descrição: Criação da tabela de conferência de produtos recebidos

CREATE TABLE `conferencia_produtos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_nf_id` INT(11) NOT NULL,
  `usuario_conferente` INT(11) NOT NULL,
  `quantidade_esperada` INT(11) NOT NULL,
  `quantidade_recebida` INT(11) NOT NULL,
  `quantidade_avariada` INT(11) NULL DEFAULT 0,
  `quantidade_devolvida` INT(11) NULL DEFAULT 0,
  `status_qualidade` ENUM('excelente', 'bom', 'regular', 'ruim', 'inutilizavel') NOT NULL DEFAULT 'bom' COLLATE 'utf8mb4_unicode_ci',
  `status_integridade` ENUM('integro', 'leve_dano', 'dano_moderado', 'dano_severo', 'inutilizavel') NOT NULL DEFAULT 'integro' COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `data_conferencia` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_item_nf_id` (`item_nf_id`) USING BTREE,
  INDEX `idx_usuario_conferente` (`usuario_conferente`) USING BTREE,
  INDEX `idx_data_conferencia` (`data_conferencia`) USING BTREE,
  CONSTRAINT `fk_conferencia_item_nf` FOREIGN KEY (`item_nf_id`) REFERENCES `pedidos_nf` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_conferencia_usuario` FOREIGN KEY (`usuario_conferente`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB; 