-- Migration: 025_create_etiquetas_impressao.sql
-- Descrição: Criação da tabela de etiquetas para impressão

CREATE TABLE `etiquetas_impressao` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_etiqueta` VARCHAR(50) NOT NULL,
  `tipo_etiqueta` ENUM('localizacao', 'palete', 'caixa', 'produto', 'armazenagem') NOT NULL,
  `item_id` INT(11) NULL DEFAULT NULL,
  `armazenagem_id` INT(11) NULL DEFAULT NULL,
  `produto_id` INT(11) NULL DEFAULT NULL,
  `conteudo` JSON NOT NULL,
  `status_impressao` ENUM('pendente', 'impressa', 'cancelada') NOT NULL DEFAULT 'pendente',
  `usuario_solicitante` INT(11) NOT NULL,
  `data_solicitacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_impressao` DATETIME NULL DEFAULT NULL,
  `quantidade_impressa` INT(11) NOT NULL DEFAULT 1,
  `observacoes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_codigo_etiqueta` (`codigo_etiqueta`),
  KEY `idx_tipo` (`tipo_etiqueta`),
  KEY `idx_item` (`item_id`),
  KEY `idx_armazenagem` (`armazenagem_id`),
  KEY `idx_produto` (`produto_id`),
  KEY `idx_status` (`status_impressao`),
  KEY `idx_solicitante` (`usuario_solicitante`),
  KEY `idx_data_solicitacao` (`data_solicitacao`),
  CONSTRAINT `fk_etiqueta_item` FOREIGN KEY (`item_id`) REFERENCES `itens_nf` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_etiqueta_armazenagem` FOREIGN KEY (`armazenagem_id`) REFERENCES `armazenagens` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_etiqueta_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_etiqueta_solicitante` FOREIGN KEY (`usuario_solicitante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 