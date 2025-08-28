-- Migration para corrigir charset e collation das tabelas existentes
-- Esta migration corrige os problemas de charset que estão causando erros

-- Corrigir charset da tabela cargos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cargos');
IF @table_exists > 0 THEN
  ALTER TABLE `cargos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela cargo_permissoes
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cargo_permissoes');
IF @table_exists > 0 THEN
  ALTER TABLE `cargo_permissoes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela usuario_cargos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuario_cargos');
IF @table_exists > 0 THEN
  ALTER TABLE `usuario_cargos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela permissoes
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'permissoes');
IF @table_exists > 0 THEN
  ALTER TABLE `permissoes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela usuarios
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
IF @table_exists > 0 THEN
  ALTER TABLE `usuarios` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela produtos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
IF @table_exists > 0 THEN
  ALTER TABLE `produtos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela produtos_variations
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
IF @table_exists > 0 THEN
  ALTER TABLE `produtos_variations` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela pedidos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pedidos');
IF @table_exists > 0 THEN
  ALTER TABLE `pedidos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela armazenagens
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'armazenagens');
IF @table_exists > 0 THEN
  ALTER TABLE `armazenagens` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela movimentacoes
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'movimentacoes');
IF @table_exists > 0 THEN
  ALTER TABLE `movimentacoes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela notas_fiscais
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'notas_fiscais');
IF @table_exists > 0 THEN
  ALTER TABLE `notas_fiscais` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela categorias
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'categorias');
IF @table_exists > 0 THEN
  ALTER TABLE `categorias` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela cores
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cores');
IF @table_exists > 0 THEN
  ALTER TABLE `cores` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela avarias
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'avarias');
IF @table_exists > 0 THEN
  ALTER TABLE `avarias` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela controle_qualidade
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'controle_qualidade');
IF @table_exists > 0 THEN
  ALTER TABLE `controle_qualidade` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela conferencia_produtos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'conferencia_produtos');
IF @table_exists > 0 THEN
  ALTER TABLE `conferencia_produtos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela movimentacoes_internas
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'movimentacoes_internas');
IF @table_exists > 0 THEN
  ALTER TABLE `movimentacoes_internas` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela etiquetas_internas
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'etiquetas_internas');
IF @table_exists > 0 THEN
  ALTER TABLE `etiquetas_internas` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela armazenagem_transferencias
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'armazenagem_transferencias');
IF @table_exists > 0 THEN
  ALTER TABLE `armazenagem_transferencias` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;

-- Corrigir charset da tabela log_acessos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'log_acessos');
IF @table_exists > 0 THEN
  ALTER TABLE `log_acessos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
END IF;
