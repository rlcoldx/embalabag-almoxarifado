-- Criar tabela cores
CREATE TABLE IF NOT EXISTS `cores` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `cor_nome` VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
    `cor_codigo` VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
    `cor` VARCHAR(50) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
    `date_update` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `date_create` TIMESTAMP NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `idx_cor_nome` (`cor_nome`),
    INDEX `idx_cor_codigo` (`cor_codigo`)
) COLLATE='latin1_swedish_ci' ENGINE=InnoDB AUTO_INCREMENT=1; 