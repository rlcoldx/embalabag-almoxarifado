-- Migration: 004_create_log_acessos_table.sql
-- Descrição: Criação da tabela de log de acessos ao sistema

CREATE TABLE IF NOT EXISTS `log_acessos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` text,
  `tipo_acesso` enum('login','logout','falha_login','timeout') NOT NULL,
  `status` enum('sucesso','falha') NOT NULL,
  `mensagem` text,
  `data_acesso` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_email` (`email`),
  KEY `idx_data_acesso` (`data_acesso`),
  KEY `idx_tipo_acesso` (`tipo_acesso`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_log_acessos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 