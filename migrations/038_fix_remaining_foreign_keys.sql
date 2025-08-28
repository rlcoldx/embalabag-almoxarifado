-- Migration para corrigir problemas restantes de foreign keys
-- Esta migration corrige as tabelas que ainda têm problemas de foreign keys

-- Corrigir tabela itens_pedido
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'itens_pedido');

IF @table_exists > 0 THEN
  -- Verificar se as foreign keys existem
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_itens_pedido_pedido' AND table_name = 'itens_pedido');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela pedidos existe
    SET @pedidos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pedidos');
    IF @pedidos_exists > 0 THEN
      ALTER TABLE `itens_pedido`
      ADD CONSTRAINT `fk_itens_pedido_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_itens_pedido_produto' AND table_name = 'itens_pedido');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos existe
    SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
    IF @produtos_exists > 0 THEN
      ALTER TABLE `itens_pedido`
      ADD CONSTRAINT `fk_itens_pedido_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_itens_pedido_variacao' AND table_name = 'itens_pedido');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos_variations existe
    SET @variations_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
    IF @variations_exists > 0 THEN
      ALTER TABLE `itens_pedido`
      ADD CONSTRAINT `fk_itens_pedido_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `produtos_variations` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
END IF;

-- Corrigir tabela etiquetas_impressao
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'etiquetas_impressao');

IF @table_exists > 0 THEN
  -- Verificar se as foreign keys existem
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_etiquetas_impressao_produto' AND table_name = 'etiquetas_impressao');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos existe
    SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
    IF @produtos_exists > 0 THEN
      ALTER TABLE `etiquetas_impressao`
      ADD CONSTRAINT `fk_etiquetas_impressao_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_etiquetas_impressao_variacao' AND table_name = 'etiquetas_impressao');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos_variations existe
    SET @variations_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
    IF @variations_exists > 0 THEN
      ALTER TABLE `etiquetas_impressao`
      ADD CONSTRAINT `fk_etiquetas_impressao_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `produtos_variations` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_etiquetas_impressao_usuario' AND table_name = 'etiquetas_impressao');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela usuarios existe
    SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
    IF @usuarios_exists > 0 THEN
      ALTER TABLE `etiquetas_impressao`
      ADD CONSTRAINT `fk_etiquetas_impressao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
END IF;

-- Corrigir tabela itens_nfe (se existir)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'itens_nfe');

IF @table_exists > 0 THEN
  -- Verificar se as foreign keys existem
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
      ADD CONSTRAINT `fk_itens_nfe_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_itens_nfe_variacao' AND table_name = 'itens_nfe');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos_variations existe
    SET @variations_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
    IF @variations_exists > 0 THEN
      ALTER TABLE `itens_nfe`
      ADD CONSTRAINT `fk_itens_nfe_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `produtos_variations` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
END IF;

-- Corrigir tabela armazenagem_transferencias
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'armazenagem_transferencias');

IF @table_exists > 0 THEN
  -- Verificar se as foreign keys existem
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_transferencias_origem' AND table_name = 'armazenagem_transferencias');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela armazenagens existe
    SET @armazenagens_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'armazenagens');
    IF @armazenagens_exists > 0 THEN
      ALTER TABLE `armazenagem_transferencias`
      ADD CONSTRAINT `fk_transferencias_origem` FOREIGN KEY (`armazenagem_origem_id`) REFERENCES `armazenagens` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_transferencias_destino' AND table_name = 'armazenagem_transferencias');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela armazenagens existe
    SET @armazenagens_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'armazenagens');
    IF @armazenagens_exists > 0 THEN
      ALTER TABLE `armazenagem_transferencias`
      ADD CONSTRAINT `fk_transferencias_destino` FOREIGN KEY (`armazenagem_destino_id`) REFERENCES `armazenagens` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints
                    WHERE constraint_name = 'fk_transferencias_usuario' AND table_name = 'armazenagem_transferencias');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela usuarios existe
    SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
    IF @usuarios_exists > 0 THEN
      ALTER TABLE `armazenagem_transferencias`
      ADD CONSTRAINT `fk_transferencias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
END IF;
