-- Criar tabela para conferência de produtos recebidos
CREATE TABLE IF NOT EXISTS `conferencia_recebimento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nfe_id` int(11) DEFAULT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `variacao_id` int(11) DEFAULT NULL,
  `quantidade_prevista` int(11) DEFAULT 0,
  `quantidade_recebida` int(11) DEFAULT 0,
  `quantidade_conferida` int(11) DEFAULT 0,
  `status_qualidade` enum('aprovado','reprovado','pendente') DEFAULT 'pendente',
  `status_integridade` enum('integro','danificado','parcialmente_danificado') DEFAULT 'integro',
  `observacoes_qualidade` text DEFAULT NULL,
  `observacoes_integridade` text DEFAULT NULL,
  `fotos_danificacao` text DEFAULT NULL,
  `usuario_conferente_id` int(11) DEFAULT NULL,
  `data_conferencia` timestamp NULL DEFAULT current_timestamp(),
  `status_conferencia` enum('pendente','em_andamento','concluida','cancelada') DEFAULT 'pendente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nfe_id` (`nfe_id`),
  KEY `idx_produto_id` (`produto_id`),
  KEY `idx_variacao_id` (`variacao_id`),
  KEY `idx_status_conferencia` (`status_conferencia`),
  KEY `idx_data_conferencia` (`data_conferencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela para itens da conferência
CREATE TABLE IF NOT EXISTS `conferencia_recebimento_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conferencia_id` int(11) DEFAULT NULL,
  `item_nfe_id` int(11) DEFAULT NULL,
  `quantidade_prevista` int(11) DEFAULT 0,
  `quantidade_recebida` int(11) DEFAULT 0,
  `quantidade_conferida` int(11) DEFAULT 0,
  `quantidade_aprovada` int(11) DEFAULT 0,
  `quantidade_rejeitada` int(11) DEFAULT 0,
  `motivo_rejeicao` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_conferencia_id` (`conferencia_id`),
  KEY `idx_item_nfe_id` (`item_nfe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela para histórico de conferências
CREATE TABLE IF NOT EXISTS `conferencia_recebimento_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conferencia_id` int(11) DEFAULT NULL,
  `acao` varchar(100) DEFAULT NULL,
  `dados_anteriores` text DEFAULT NULL,
  `dados_novos` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_acao` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_conferencia_id` (`conferencia_id`),
  KEY `idx_usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar foreign keys se as tabelas existirem
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'conferencia_recebimento') > 0,
    'ALTER TABLE `conferencia_recebimento` ADD CONSTRAINT `fk_conferencia_nfe` FOREIGN KEY (`nfe_id`) REFERENCES `notas_fiscais_eletronicas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "Tabela conferencia_recebimento não existe ainda" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'conferencia_recebimento') > 0,
    'ALTER TABLE `conferencia_recebimento` ADD CONSTRAINT `fk_conferencia_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "Tabela conferencia_recebimento não existe ainda" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'conferencia_recebimento') > 0,
    'ALTER TABLE `conferencia_recebimento` ADD CONSTRAINT `fk_conferencia_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `produtos_variations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "Tabela conferencia_recebimento não existe ainda" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'conferencia_recebimento') > 0,
    'ALTER TABLE `conferencia_recebimento` ADD CONSTRAINT `fk_conferencia_usuario` FOREIGN KEY (`usuario_conferente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "Tabela conferencia_recebimento não existe ainda" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
