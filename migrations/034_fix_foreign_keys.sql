-- Migration para corrigir problemas de foreign keys
-- Esta migration corrige as constraints que estão causando erros

-- Corrigir foreign key da tabela itens_pedido
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'itens_pedido');
IF @table_exists > 0 THEN
  -- Verificar se a constraint já existe
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
  
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_itens_pedido_produto' AND table_name = 'itens_pedido');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos existe
    SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
    IF @produtos_exists > 0 THEN
      ALTER TABLE `itens_pedido` 
      ADD CONSTRAINT `fk_itens_pedido_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
    END IF;
  END IF;
END IF;

-- Corrigir foreign key da tabela etiquetas_impressao
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'etiquetas_impressao');
IF @table_exists > 0 THEN
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_etiquetas_produto' AND table_name = 'etiquetas_impressao');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos existe
    SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
    IF @produtos_exists > 0 THEN
      ALTER TABLE `etiquetas_impressao` 
      ADD CONSTRAINT `fk_etiquetas_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
    END IF;
  END IF;
  
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_etiquetas_armazenagem' AND table_name = 'etiquetas_impressao');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela armazenagens existe
    SET @armazenagens_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'armazenagens');
    IF @armazenagens_exists > 0 THEN
      ALTER TABLE `etiquetas_impressao` 
      ADD CONSTRAINT `fk_etiquetas_armazenagem` FOREIGN KEY (`armazenagem_id`) REFERENCES `armazenagens` (`id`);
    END IF;
  END IF;
END IF;

-- Corrigir foreign key da tabela movimentacoes_estoque
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'movimentacoes_estoque');
IF @table_exists > 0 THEN
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_mov_estoque_produto' AND table_name = 'movimentacoes_estoque');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos existe
    SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
    IF @produtos_exists > 0 THEN
      ALTER TABLE `movimentacoes_estoque` 
      ADD CONSTRAINT `fk_mov_estoque_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
    END IF;
  END IF;
  
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_mov_estoque_variacao' AND table_name = 'movimentacoes_estoque');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos_variations existe
    SET @variations_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
    IF @variations_exists > 0 THEN
      ALTER TABLE `movimentacoes_estoque` 
      ADD CONSTRAINT `fk_mov_estoque_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `produtos_variations` (`id`);
    END IF;
  END IF;
END IF;

-- Corrigir foreign key da tabela cores
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cores');
IF @table_exists > 0 THEN
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_cores_produto' AND table_name = 'cores');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela produtos existe
    SET @produtos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
    IF @produtos_exists > 0 THEN
      ALTER TABLE `cores` 
      ADD CONSTRAINT `fk_cores_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
    END IF;
  END IF;
END IF;

-- Corrigir foreign key da tabela usuario_permissoes
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuario_permissoes');
IF @table_exists > 0 THEN
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_usuario_permissoes_usuario' AND table_name = 'usuario_permissoes');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela usuarios existe
    SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
    IF @usuarios_exists > 0 THEN
      ALTER TABLE `usuario_permissoes` 
      ADD CONSTRAINT `fk_usuario_permissoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_usuario_permissoes_permissao' AND table_name = 'usuario_permissoes');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela permissoes existe
    SET @permissoes_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'permissoes');
    IF @permissoes_exists > 0 THEN
      ALTER TABLE `usuario_permissoes` 
      ADD CONSTRAINT `fk_usuario_permissoes_permissao` FOREIGN KEY (`permissao_id`) REFERENCES `permissoes` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
END IF;

-- Corrigir foreign key da tabela cargo_permissoes
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cargo_permissoes');
IF @table_exists > 0 THEN
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_cargo_permissoes_cargo' AND table_name = 'cargo_permissoes');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela cargos existe
    SET @cargos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cargos');
    IF @cargos_exists > 0 THEN
      ALTER TABLE `cargo_permissoes` 
      ADD CONSTRAINT `fk_cargo_permissoes_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_cargo_permissoes_permissao' AND table_name = 'cargo_permissoes');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela permissoes existe
    SET @permissoes_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'permissoes');
    IF @permissoes_exists > 0 THEN
      ALTER TABLE `cargo_permissoes` 
      ADD CONSTRAINT `fk_cargo_permissoes_permissao` FOREIGN KEY (`permissao_id`) REFERENCES `permissoes` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
END IF;

-- Corrigir foreign key da tabela usuario_cargos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuario_cargos');
IF @table_exists > 0 THEN
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_usuario_cargos_usuario' AND table_name = 'usuario_cargos');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela usuarios existe
    SET @usuarios_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
    IF @usuarios_exists > 0 THEN
      ALTER TABLE `usuario_cargos` 
      ADD CONSTRAINT `fk_usuario_cargos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
  
  -- Verificar se a constraint já existe
  SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                    WHERE constraint_name = 'fk_usuario_cargos_cargo' AND table_name = 'usuario_cargos');
  
  IF @fk_exists = 0 THEN
    -- Verificar se a tabela cargos existe
    SET @cargos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cargos');
    IF @cargos_exists > 0 THEN
      ALTER TABLE `usuario_cargos` 
      ADD CONSTRAINT `fk_usuario_cargos_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE;
    END IF;
  END IF;
END IF;
