-- Migration: 024_create_controle_qualidade.sql
-- Descrição: Criação da tabela de controle de qualidade

CREATE TABLE `controle_qualidade` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `usuario_inspetor` INT(11) NOT NULL,
  `data_inspecao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_qualidade` ENUM('aprovado', 'reprovado', 'condicional') NOT NULL,
  `quantidade_inspecionada` INT(11) NOT NULL,
  `quantidade_aprovada` INT(11) NOT NULL,
  `quantidade_reprovada` INT(11) NOT NULL DEFAULT 0,
  `motivo_reprovacao` TEXT NULL DEFAULT NULL,
  `observacoes` TEXT NULL DEFAULT NULL,
  `fotos_evidencia` JSON NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item` (`item_id`),
  KEY `idx_inspetor` (`usuario_inspetor`),
  KEY `idx_status` (`status_qualidade`),
  KEY `idx_data_inspecao` (`data_inspecao`),
  CONSTRAINT `fk_qualidade_item` FOREIGN KEY (`item_id`) REFERENCES `itens_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_qualidade_inspetor` FOREIGN KEY (`usuario_inspetor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 