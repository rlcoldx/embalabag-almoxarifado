-- Migration: 023_create_armazenagem_transferencias.sql
-- Descrição: Criação da tabela de transferências entre armazenagens

CREATE TABLE `armazenagem_transferencias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `armazenagem_origem` INT(11) NOT NULL,
  `armazenagem_destino` INT(11) NOT NULL,
  `quantidade` INT(11) NOT NULL,
  `motivo` ENUM('reorganizacao', 'otimizacao_espaco', 'manutencao', 'outro') NOT NULL DEFAULT 'reorganizacao',
  `observacoes` TEXT NULL DEFAULT NULL,
  `status` ENUM('pendente', 'em_andamento', 'concluida', 'cancelada') NOT NULL DEFAULT 'pendente',
  `usuario_solicitante` INT(11) NOT NULL,
  `usuario_executor` INT(11) NULL DEFAULT NULL,
  `data_solicitacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_execucao` DATETIME NULL DEFAULT NULL,
  `data_conclusao` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item` (`item_id`),
  KEY `idx_origem` (`armazenagem_origem`),
  KEY `idx_destino` (`armazenagem_destino`),
  KEY `idx_status` (`status`),
  KEY `idx_solicitante` (`usuario_solicitante`),
  KEY `idx_executor` (`usuario_executor`),
  KEY `idx_data_solicitacao` (`data_solicitacao`),
  CONSTRAINT `fk_transferencia_item` FOREIGN KEY (`item_id`) REFERENCES `itens_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transferencia_origem` FOREIGN KEY (`armazenagem_origem`) REFERENCES `armazenagens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transferencia_destino` FOREIGN KEY (`armazenagem_destino`) REFERENCES `armazenagens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transferencia_solicitante` FOREIGN KEY (`usuario_solicitante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transferencia_executor` FOREIGN KEY (`usuario_executor`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 