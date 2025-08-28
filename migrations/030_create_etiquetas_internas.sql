-- Migration: 030_create_etiquetas_internas.sql
-- Descrição: Criação da tabela de etiquetas internas

CREATE TABLE `etiquetas_internas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `tipo_etiqueta` ENUM('localizacao', 'palete', 'caixa', 'produto', 'armazenagem') NOT NULL DEFAULT 'localizacao' COLLATE 'utf8mb4_unicode_ci',
  `referencia_id` INT(11) NULL DEFAULT NULL,
  `referencia_tipo` ENUM('armazenagem', 'item_nf', 'palete', 'caixa') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `conteudo` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `codigo_barras` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `qr_code` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usuario_criacao` INT(11) NOT NULL,
  `data_impressao` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('criada', 'impressa', 'aplicada', 'inativa') NOT NULL DEFAULT 'criada' COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `codigo` (`codigo`) USING BTREE,
  INDEX `idx_tipo_etiqueta` (`tipo_etiqueta`) USING BTREE,
  INDEX `idx_referencia` (`referencia_id`, `referencia_tipo`) USING BTREE,
  INDEX `idx_usuario_criacao` (`usuario_criacao`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE,
  INDEX `idx_data_impressao` (`data_impressao`) USING BTREE,
  CONSTRAINT `fk_etiqueta_usuario` FOREIGN KEY (`usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB; 