-- Adicionar coluna estoque_minimo na tabela produtos_variations
ALTER TABLE `produtos_variations` 
ADD COLUMN `estoque_minimo` int(11) DEFAULT 0 AFTER `estoque`;

-- Atualizar registros existentes com estoque mínimo padrão
UPDATE `produtos_variations` SET `estoque_minimo` = 1 WHERE `estoque_minimo` IS NULL OR `estoque_minimo` = 0; 