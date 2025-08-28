-- Migration: 010_create_armazenagens_table.sql
-- Descrição: Criação da tabela de armazenagens/endereços do almoxarifado

CREATE TABLE `armazenagens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `descricao` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `tipo` ENUM('prateleira', 'gaveta', 'caixa', 'pallet', 'area') NOT NULL DEFAULT 'prateleira' COLLATE 'utf8mb4_unicode_ci',
  `setor` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `corredor` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `posicao` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `nivel` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `capacidade_maxima` INT(11) NULL DEFAULT NULL,
  `capacidade_atual` INT(11) NULL DEFAULT 0,
  `status` ENUM('ativo', 'inativo', 'bloqueado', 'manutencao') NOT NULL DEFAULT 'ativo' COLLATE 'utf8mb4_unicode_ci',
  `observacoes` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `codigo` (`codigo`) USING BTREE,
  INDEX `idx_tipo` (`tipo`) USING BTREE,
  INDEX `idx_setor` (`setor`) USING BTREE,
  INDEX `idx_status` (`status`) USING BTREE
) COLLATE = 'utf8mb4_unicode_ci' ENGINE = InnoDB;