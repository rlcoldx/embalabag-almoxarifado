CREATE TABLE IF NOT EXISTS `movimentacoes_estoque` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produto` bigint(20) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL DEFAULT 'entrada',
  `quantidade` int(11) NOT NULL,
  `observacao` text,
  `data_movimentacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_produto` (`id_produto`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_data` (`data_movimentacao`),
  KEY `idx_usuario` (`usuario_id`),
  CONSTRAINT `fk_movestoque_produto` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_movestoque_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;