-- Migration para corrigir problemas de transações
-- Esta migration remove comandos de transação problemáticos

-- Corrigir migration 004_create_log_acessos_table.sql
-- Remover BEGIN e COMMIT se existirem e usar CREATE TABLE IF NOT EXISTS
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'log_acessos');

IF @table_exists = 0 THEN
  CREATE TABLE IF NOT EXISTS `log_acessos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` int(11) NOT NULL,
    `data_acesso` datetime NOT NULL,
    `ip_acesso` varchar(45) DEFAULT NULL,
    `user_agent` text,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `usuario_id` (`usuario_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  
  -- Adicionar foreign key se a tabela usuarios existir
  SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
  IF @usuarios_exists > 0 THEN
    ALTER TABLE `log_acessos` 
    ADD CONSTRAINT `fk_log_acessos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
  END IF;
END IF;

-- Corrigir migration 020_create_movimentacoes_estoque_table.sql
-- Remover BEGIN e COMMIT se existirem e usar CREATE TABLE IF NOT EXISTS
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'movimentacoes_estoque');

IF @table_exists = 0 THEN
  CREATE TABLE IF NOT EXISTS `movimentacoes_estoque` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `produto_id` int(11) NOT NULL,
    `variacao_id` int(11) NOT NULL,
    `tipo_movimentacao` enum('entrada','saida','ajuste') NOT NULL,
    `quantidade` int(11) NOT NULL,
    `motivo` text,
    `usuario_id` int(11) NOT NULL,
    `data_movimentacao` datetime NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `produto_id` (`produto_id`),
    KEY `variacao_id` (`variacao_id`),
    KEY `usuario_id` (`usuario_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  
  -- Adicionar foreign keys se as tabelas existirem
  SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
  IF @produtos_exists > 0 THEN
    ALTER TABLE `movimentacoes_estoque` 
    ADD CONSTRAINT `fk_movimentacoes_estoque_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
  END IF;
  
  SET @variations_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
  IF @variations_exists > 0 THEN
    ALTER TABLE `movimentacoes_estoque` 
    ADD CONSTRAINT `fk_movimentacoes_estoque_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `produtos_variations` (`id`) ON DELETE CASCADE;
  END IF;
  
  SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
  IF @usuarios_exists > 0 THEN
    ALTER TABLE `movimentacoes_estoque` 
    ADD CONSTRAINT `fk_movimentacoes_estoque_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
  END IF;
END IF;

-- Corrigir migration 026_create_cores_table.sql
-- Remover BEGIN e COMMIT se existirem e usar CREATE TABLE IF NOT EXISTS
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cores');

IF @table_exists = 0 THEN
  CREATE TABLE IF NOT EXISTS `cores` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `codigo_hex` varchar(7) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `nome` (`nome`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  
  -- Inserir cores padrão se não existirem
  INSERT IGNORE INTO `cores` (`nome`, `codigo_hex`) VALUES
  ('Preto', '#000000'),
  ('Branco', '#FFFFFF'),
  ('Vermelho', '#FF0000'),
  ('Azul', '#0000FF'),
  ('Verde', '#00FF00'),
  ('Amarelo', '#FFFF00'),
  ('Rosa', '#FFC0CB'),
  ('Laranja', '#FFA500'),
  ('Roxo', '#800080'),
  ('Marrom', '#A52A2A');
END IF;
