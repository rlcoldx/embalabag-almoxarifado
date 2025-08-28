-- Migration: 031_insert_permissoes_recebimento.sql
-- Descrição: Inserir permissões relacionadas ao recebimento e armazenagem

INSERT INTO `permissoes` (`nome`, `descricao`, `modulo`, `acao`, `created_at`, `updated_at`) VALUES
-- Notas Fiscais
('Visualizar Notas Fiscais', 'Permite visualizar notas fiscais', 'notas_fiscais', 'visualizar', NOW(), NOW()),
('Criar Nota Fiscal', 'Permite criar nova nota fiscal', 'notas_fiscais', 'criar', NOW(), NOW()),
('Editar Nota Fiscal', 'Permite editar nota fiscal', 'notas_fiscais', 'editar', NOW(), NOW()),
('Excluir Nota Fiscal', 'Permite excluir nota fiscal', 'notas_fiscais', 'excluir', NOW(), NOW()),
('Receber Nota Fiscal', 'Permite marcar nota fiscal como recebida', 'notas_fiscais', 'receber', NOW(), NOW()),

-- Conferência
('Visualizar Conferências', 'Permite visualizar conferências de produtos', 'conferencia', 'visualizar', NOW(), NOW()),
('Realizar Conferência', 'Permite realizar conferência de produtos', 'conferencia', 'realizar', NOW(), NOW()),
('Editar Conferência', 'Permite editar conferência realizada', 'conferencia', 'editar', NOW(), NOW()),
('Aprovar Conferência', 'Permite aprovar conferência realizada', 'conferencia', 'aprovar', NOW(), NOW()),

-- Movimentações
('Visualizar Movimentações', 'Permite visualizar movimentações internas', 'movimentacoes', 'visualizar', NOW(), NOW()),
('Criar Movimentação', 'Permite criar nova movimentação', 'movimentacoes', 'criar', NOW(), NOW()),
('Executar Movimentação', 'Permite executar movimentação', 'movimentacoes', 'executar', NOW(), NOW()),
('Cancelar Movimentação', 'Permite cancelar movimentação', 'movimentacoes', 'cancelar', NOW(), NOW()),

-- Etiquetas
('Visualizar Etiquetas', 'Permite visualizar etiquetas internas', 'etiquetas', 'visualizar', NOW(), NOW()),
('Criar Etiqueta', 'Permite criar nova etiqueta', 'etiquetas', 'criar', NOW(), NOW()),
('Imprimir Etiqueta', 'Permite imprimir etiqueta', 'etiquetas', 'imprimir', NOW(), NOW()),
('Editar Etiqueta', 'Permite editar etiqueta', 'etiquetas', 'editar', NOW(), NOW()),
('Excluir Etiqueta', 'Permite excluir etiqueta', 'etiquetas', 'excluir', NOW(), NOW()),

-- Relatórios
('Relatório de Recebimento', 'Permite gerar relatórios de recebimento', 'relatorios', 'recebimento', NOW(), NOW()),
('Relatório de Conferência', 'Permite gerar relatórios de conferência', 'relatorios', 'conferencia', NOW(), NOW()),
('Relatório de Movimentação', 'Permite gerar relatórios de movimentação', 'relatorios', 'movimentacao', NOW(), NOW()); 