-- Migration: 043_insert_permissoes_armazenagens_cargos.sql
-- Descrição: Inserir permissões para Armazenagens e Cargos

-- Permissões para Armazenagens
INSERT IGNORE INTO `permissoes` (`nome`, `descricao`, `modulo`, `acao`, `created_at`) VALUES
('Visualizar Armazenagens', 'Permite visualizar lista de armazenagens', 'armazenagens', 'visualizar', NOW()),
('Criar Armazenagem', 'Permite criar nova armazenagem', 'armazenagens', 'criar', NOW()),
('Editar Armazenagem', 'Permite editar armazenagem existente', 'armazenagens', 'editar', NOW()),
('Excluir Armazenagem', 'Permite excluir armazenagem', 'armazenagens', 'excluir', NOW()),
('Visualizar Mapa de Armazenagens', 'Permite visualizar mapa de armazenagens', 'armazenagens', 'mapa', NOW()),
('Transferir entre Armazenagens', 'Permite transferir produtos entre armazenagens', 'armazenagens', 'transferir', NOW());

-- Permissões para Cargos
INSERT IGNORE INTO `permissoes` (`nome`, `descricao`, `modulo`, `acao`, `created_at`) VALUES
('Visualizar Cargos', 'Permite visualizar lista de cargos', 'cargos', 'visualizar', NOW()),
('Criar Cargo', 'Permite criar novo cargo', 'cargos', 'criar', NOW()),
('Editar Cargo', 'Permite editar cargo existente', 'cargos', 'editar', NOW()),
('Excluir Cargo', 'Permite excluir cargo', 'cargos', 'excluir', NOW()),
('Gerenciar Permissões do Cargo', 'Permite gerenciar permissões de um cargo', 'cargos', 'permissoes', NOW());

-- Atualizar permissões do cargo Administrador (ID: 1) para incluir as novas permissões
INSERT IGNORE INTO `cargo_permissoes` (`cargo_id`, `permissao_id`)
SELECT 1, p.id FROM `permissoes` p 
WHERE p.modulo IN ('armazenagens', 'cargos');

-- Atualizar permissões do cargo Gerente (ID: 2) para incluir visualização de armazenagens e cargos
INSERT IGNORE INTO `cargo_permissoes` (`cargo_id`, `permissao_id`)
SELECT 2, p.id FROM `permissoes` p 
WHERE p.modulo IN ('armazenagens', 'cargos') 
AND p.acao IN ('visualizar', 'mapa');

-- Atualizar permissões do cargo Operador (ID: 3) para incluir visualização de armazenagens
INSERT IGNORE INTO `cargo_permissoes` (`cargo_id`, `permissao_id`)
SELECT 3, p.id FROM `permissoes` p 
WHERE p.modulo = 'armazenagens' 
AND p.acao IN ('visualizar', 'mapa');

-- Atualizar permissões do cargo Analista (ID: 4) para incluir visualização de armazenagens e cargos
INSERT IGNORE INTO `cargo_permissoes` (`cargo_id`, `permissao_id`)
SELECT 4, p.id FROM `permissoes` p 
WHERE p.modulo IN ('armazenagens', 'cargos') 
AND p.acao IN ('visualizar', 'mapa');

-- Atualizar permissões do cargo Auxiliar (ID: 5) para incluir visualização de armazenagens
INSERT IGNORE INTO `cargo_permissoes` (`cargo_id`, `permissao_id`)
SELECT 5, p.id FROM `permissoes` p 
WHERE p.modulo = 'armazenagens' 
AND p.acao = 'visualizar';
