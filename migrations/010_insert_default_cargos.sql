-- Inserir cargos padrão
INSERT INTO cargos (nome, descricao, status) VALUES
('Administrador', 'Acesso total ao sistema', 'ativo'),
('Gerente', 'Gerencia equipes e processos', 'ativo'),
('Operador', 'Operações básicas do sistema', 'ativo'),
('Analista', 'Análise e relatórios', 'ativo'),
('Auxiliar', 'Auxiliar administrativo', 'ativo');

-- Inserir permissões para o cargo Administrador (todas as permissões)
INSERT INTO cargo_permissoes (cargo_id, permissao_id)
SELECT 1, id FROM permissoes;

-- Inserir permissões básicas para outros cargos
INSERT INTO cargo_permissoes (cargo_id, permissao_id)
SELECT 2, id FROM permissoes WHERE modulo IN ('usuarios', 'relatorios', 'dashboard');

INSERT INTO cargo_permissoes (cargo_id, permissao_id)
SELECT 3, id FROM permissoes WHERE modulo IN ('dashboard', 'relatorios') AND acao IN ('visualizar');

INSERT INTO cargo_permissoes (cargo_id, permissao_id)
SELECT 4, id FROM permissoes WHERE modulo IN ('dashboard', 'relatorios', 'usuarios') AND acao IN ('visualizar', 'editar');

INSERT INTO cargo_permissoes (cargo_id, permissao_id)
SELECT 5, id FROM permissoes WHERE modulo IN ('dashboard') AND acao IN ('visualizar');