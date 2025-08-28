-- Migration para corrigir problemas de colunas duplicadas
-- Esta migration remove colunas duplicadas que estão causando erros

-- Verificar e corrigir coluna estoque_minimo duplicada em produtos_variations
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
IF @table_exists > 0 THEN
  -- Verificar se a coluna estoque_minimo existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'produtos_variations' AND column_name = 'estoque_minimo');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `produtos_variations` ADD COLUMN `estoque_minimo` int(11) DEFAULT 0;
  END IF;
END IF;

-- Verificar e corrigir coluna pedido_id duplicada em notas_fiscais
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'notas_fiscais');
IF @table_exists > 0 THEN
  -- Verificar se a coluna pedido_id existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'notas_fiscais' AND column_name = 'pedido_id');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `notas_fiscais` ADD COLUMN `pedido_id` int(11) DEFAULT NULL;
    
    -- Adicionar foreign key se a tabela pedidos existir
    SET @pedidos_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pedidos');
    IF @pedidos_exists > 0 THEN
      ALTER TABLE `notas_fiscais` 
      ADD CONSTRAINT `fk_notas_fiscais_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL;
    END IF;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em permissoes
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'permissoes');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'permissoes' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `permissoes` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em usuarios
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'usuarios' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `usuarios` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em cargos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cargos');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'cargos' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `cargos` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em produtos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'produtos' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `produtos` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em produtos_variations
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'produtos_variations');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'produtos_variations' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `produtos_variations` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em pedidos
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pedidos');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'pedidos' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `pedidos` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em armazenagens
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'armazenagens');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'armazenagens' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `armazenagens` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;

-- Verificar e corrigir coluna updated_at em movimentacoes
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'movimentacoes');
IF @table_exists > 0 THEN
  -- Verificar se a coluna updated_at existe
  SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() AND table_name = 'movimentacoes' AND column_name = 'updated_at');
  
  -- Se a coluna não existir, adicionar
  IF @column_exists = 0 THEN
    ALTER TABLE `movimentacoes` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
  END IF;
END IF;
