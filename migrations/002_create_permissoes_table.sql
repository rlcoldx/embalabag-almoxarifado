-- Migration: 002_create_permissoes_table.sql
-- Descrição: Criação da tabela de permissões do sistema

CREATE TABLE IF NOT EXISTS `permissoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `modulo` varchar(50) NOT NULL COMMENT 'Módulo do sistema (usuarios, produtos, estoque, etc)',
  `acao` varchar(50) NOT NULL COMMENT 'Ação (criar, editar, excluir, visualizar)',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_modulo_acao` (`modulo`, `acao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir permissões básicas do sistema
INSERT INTO `permissoes` (`nome`, `descricao`, `modulo`, `acao`) VALUES
('Visualizar Usuários', 'Permite visualizar lista de usuários', 'usuarios', 'visualizar'),
('Criar Usuários', 'Permite criar novos usuários', 'usuarios', 'criar'),
('Editar Usuários', 'Permite editar usuários existentes', 'usuarios', 'editar'),
('Excluir Usuários', 'Permite excluir usuários', 'usuarios', 'excluir'),
('Visualizar Produtos', 'Permite visualizar produtos', 'produtos', 'visualizar'),
('Criar Produtos', 'Permite criar novos produtos', 'produtos', 'criar'),
('Editar Produtos', 'Permite editar produtos', 'produtos', 'editar'),
('Excluir Produtos', 'Permite excluir produtos', 'produtos', 'excluir'),
('Visualizar Estoque', 'Permite visualizar estoque', 'estoque', 'visualizar'),
('Movimentar Estoque', 'Permite fazer movimentações no estoque', 'estoque', 'movimentar'),
('Visualizar Relatórios', 'Permite visualizar relatórios', 'relatorios', 'visualizar'),
('Gerar Relatórios', 'Permite gerar relatórios', 'relatorios', 'gerar'),
('Configurações do Sistema', 'Permite acessar configurações', 'configuracoes', 'acessar'); 