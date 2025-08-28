-- Migration: 001_create_usuarios_table.sql
-- Descrição: Criação da tabela de usuários com tipos e permissões

CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `email` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `senha` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `senha_padrao` VARCHAR(255) NULL DEFAULT '50a837232b0b241efb7dffc7dbb3306fd1d8b146' COLLATE 'utf8mb4_unicode_ci',
  `tipo` ENUM ('1', '2', '3', '4') NOT NULL DEFAULT '3' COMMENT '1=Admin, 2=Gerente, 3=UsuÃ¡rio, 4=Inativo' COLLATE 'utf8mb4_unicode_ci',
  `user_setor` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `cpf` VARCHAR(14) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `telefone` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `sigla` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `companhia` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `cnpj` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `cep` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `logradouro` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `numero` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `complemento` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `bairro` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `cidade` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `uf` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `cookie_key` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `status` ENUM ('ativo', 'inativo', 'bloqueado') NULL DEFAULT 'ativo' COLLATE 'utf8mb4_unicode_ci',
  `ultimo_acesso` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email` (`email`) USING BTREE,
  INDEX `idx_email` (`email`) USING BTREE,
  INDEX `idx_tipo` (`tipo`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;

-- Inserir usuário administrador padrão
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `senha_padrao`, `tipo`, `user_setor`, `status`) VALUES
('Administrador', 'admin@embalabag.com', SHA1('@Embalabag2025$$'), SHA1('@Embalabag2025$$'), '1', 'Administração', 'ativo'); 