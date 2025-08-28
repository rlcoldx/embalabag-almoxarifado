-- Migration para Notas Fiscais Eletrônicas
-- Verificar se a tabela já existe antes de criar
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'notas_fiscais_eletronicas');

SET @sql = IF(@table_exists = 0,
  'CREATE TABLE `notas_fiscais_eletronicas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `numero_nfe` varchar(50) NOT NULL,
    `chave_acesso` varchar(100) NOT NULL,
    `pedido_id` int(11) DEFAULT NULL,
    `fornecedor_id` int(11) NOT NULL,
    `data_emissao` date NOT NULL,
    `data_recebimento` datetime NOT NULL,
    `valor_total` decimal(10,2) NOT NULL,
    `status` enum("pendente","conferida","finalizada") DEFAULT "pendente",
    `observacoes` text,
    `usuario_recebimento_id` int(11) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `numero_nfe` (`numero_nfe`),
    UNIQUE KEY `chave_acesso` (`chave_acesso`),
    KEY `pedido_id` (`pedido_id`),
    KEY `fornecedor_id` (`fornecedor_id`),
    KEY `usuario_recebimento_id` (`usuario_recebimento_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
  'SELECT "Tabela notas_fiscais_eletronicas já existe" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar foreign keys se não existirem
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                  WHERE constraint_name = 'fk_nfe_pedido' AND table_name = 'notas_fiscais_eletronicas');

IF @fk_exists = 0 THEN
  -- Verificar se a tabela pedidos existe
  SET @pedidos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pedidos');
  IF @pedidos_exists > 0 THEN
    ALTER TABLE `notas_fiscais_eletronicas` 
    ADD CONSTRAINT `fk_nfe_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL;
  END IF;
END IF;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                  WHERE constraint_name = 'fk_nfe_fornecedor' AND table_name = 'notas_fiscais_eletronicas');

IF @fk_exists = 0 THEN
  -- Verificar se a tabela usuarios existe
  SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
  IF @usuarios_exists > 0 THEN
    ALTER TABLE `notas_fiscais_eletronicas` 
    ADD CONSTRAINT `fk_nfe_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `usuarios` (`id`);
  END IF;
END IF;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                  WHERE constraint_name = 'fk_nfe_usuario' AND table_name = 'notas_fiscais_eletronicas');

IF @fk_exists = 0 THEN
  -- Verificar se a tabela usuarios existe
  SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
  IF @usuarios_exists > 0 THEN
    ALTER TABLE `notas_fiscais_eletronicas` 
    ADD CONSTRAINT `fk_nfe_usuario` FOREIGN KEY (`usuario_recebimento_id`) REFERENCES `usuarios` (`id`);
  END IF;
END IF;

-- Tabela para itens da NF-e
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'itens_nfe');

SET @sql = IF(@table_exists = 0,
  'CREATE TABLE `itens_nfe` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nfe_id` int(11) NOT NULL,
    `produto_id` int(11) NOT NULL,
    `variacao_id` int(11) NOT NULL,
    `quantidade` int(11) NOT NULL,
    `valor_unitario` decimal(10,2) NOT NULL,
    `valor_total` decimal(10,2) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `nfe_id` (`nfe_id`),
    KEY `produto_id` (`produto_id`),
    KEY `variacao_id` (`variacao_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
  'SELECT "Tabela itens_nfe já existe" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar foreign keys para itens_nfe se não existirem
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                  WHERE constraint_name = 'fk_itens_nfe_nfe' AND table_name = 'itens_nfe');

IF @fk_exists = 0 THEN
  -- Verificar se a tabela notas_fiscais_eletronicas existe
  SET @nfe_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'notas_fiscais_eletronicas');
  IF @nfe_exists > 0 THEN
    ALTER TABLE `itens_nfe` 
    ADD CONSTRAINT `fk_itens_nfe_nfe` FOREIGN KEY (`nfe_id`) REFERENCES `notas_fiscais_eletronicas` (`id`) ON DELETE CASCADE;
  END IF;
END IF;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                  WHERE constraint_name = 'fk_itens_nfe_produto' AND table_name = 'itens_nfe');

IF @fk_exists = 0 THEN
  -- Verificar se a tabela produtos existe
  SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
  IF @produtos_exists > 0 THEN
    ALTER TABLE `itens_nfe` 
    ADD CONSTRAINT `fk_itens_nfe_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
  END IF;
END IF;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                  WHERE constraint_name = 'fk_itens_nfe_variacao' AND table_name = 'itens_nfe');

IF @fk_exists = 0 THEN
  -- Verificar se a tabela produtos_variations existe
  SET @variations_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
  IF @variations_exists > 0 THEN
    ALTER TABLE `itens_nfe` 
    ADD CONSTRAINT `fk_itens_nfe_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `produtos_variations` (`id`);
  END IF;
END IF;
