-- Migration para corrigir problemas de INSERT duplicados
-- Esta migration usa INSERT IGNORE para evitar erros de chaves duplicadas

-- Inserir permissões de recebimento se não existirem
INSERT IGNORE INTO `permissoes` (`modulo`, `acao`, `descricao`, `created_at`) VALUES
('recebimento', 'visualizar', 'Visualizar módulo de recebimento', NOW()),
('recebimento', 'criar', 'Criar novos recebimentos', NOW()),
('recebimento', 'editar', 'Editar recebimentos existentes', NOW()),
('recebimento', 'excluir', 'Excluir recebimentos', NOW()),
('recebimento', 'conferir', 'Conferir produtos recebidos', NOW()),
('recebimento', 'armazenar', 'Armazenar produtos recebidos', NOW()),
('recebimento', 'transferir', 'Transferir produtos entre armazenagens', NOW()),
('recebimento', 'etiquetar', 'Gerar e imprimir etiquetas', NOW());

-- Inserir cargos padrão se não existirem
INSERT IGNORE INTO `cargos` (`nome`, `descricao`, `created_at`) VALUES
('Administrador', 'Acesso total ao sistema', NOW()),
('Gerente', 'Gerencia operações e usuários', NOW()),
('Operador', 'Opera funcionalidades básicas', NOW()),
('Fornecedor', 'Acesso limitado para fornecedores', NOW());

-- Inserir permissões de usuários se não existirem
INSERT IGNORE INTO `usuario_permissoes` (`usuario_id`, `permissao_id`, `created_at`) VALUES
(1, 1, NOW()), -- Usuário 1 com permissão de visualizar usuários
(1, 13, NOW()), -- Usuário 1 com permissão de visualizar recebimento
(1, 14, NOW()), -- Usuário 1 com permissão de criar recebimento
(1, 15, NOW()), -- Usuário 1 com permissão de editar recebimento
(1, 16, NOW()), -- Usuário 1 com permissão de excluir recebimento
(1, 17, NOW()), -- Usuário 1 com permissão de conferir recebimento
(1, 18, NOW()), -- Usuário 1 com permissão de armazenar recebimento
(1, 19, NOW()), -- Usuário 1 com permissão de transferir recebimento
(1, 20, NOW()); -- Usuário 1 com permissão de etiquetar recebimento

-- Inserir permissões de cargos se não existirem
INSERT IGNORE INTO `cargo_permissoes` (`cargo_id`, `permissao_id`, `created_at`) VALUES
(1, 1, NOW()), -- Cargo Administrador com permissão de visualizar usuários
(1, 13, NOW()), -- Cargo Administrador com permissão de visualizar recebimento
(1, 14, NOW()), -- Cargo Administrador com permissão de criar recebimento
(1, 15, NOW()), -- Cargo Administrador com permissão de editar recebimento
(1, 16, NOW()), -- Cargo Administrador com permissão de excluir recebimento
(1, 17, NOW()), -- Cargo Administrador com permissão de conferir recebimento
(1, 18, NOW()), -- Cargo Administrador com permissão de armazenar recebimento
(1, 19, NOW()), -- Cargo Administrador com permissão de transferir recebimento
(1, 20, NOW()), -- Cargo Administrador com permissão de etiquetar recebimento
(2, 13, NOW()), -- Cargo Gerente com permissão de visualizar recebimento
(2, 14, NOW()), -- Cargo Gerente com permissão de criar recebimento
(2, 15, NOW()), -- Cargo Gerente com permissão de editar recebimento
(2, 17, NOW()), -- Cargo Gerente com permissão de conferir recebimento
(2, 18, NOW()), -- Cargo Gerente com permissão de armazenar recebimento
(2, 19, NOW()), -- Cargo Gerente com permissão de transferir recebimento
(2, 20, NOW()), -- Cargo Gerente com permissão de etiquetar recebimento
(3, 13, NOW()), -- Cargo Operador com permissão de visualizar recebimento
(3, 17, NOW()), -- Cargo Operador com permissão de conferir recebimento
(3, 18, NOW()), -- Cargo Operador com permissão de armazenar recebimento
(3, 20, NOW()); -- Cargo Operador com permissão de etiquetar recebimento

-- Inserir usuário no cargo se não existir
INSERT IGNORE INTO `usuario_cargos` (`usuario_id`, `cargo_id`, `created_at`) VALUES
(1, 1, NOW()); -- Usuário 1 no cargo Administrador
