-- Migration: 027_add_pedido_id_to_notas_fiscais.sql
-- Descrição: Adicionar campo pedido_id na tabela notas_fiscais para vincular NF-e a pedidos

ALTER TABLE `notas_fiscais` 
ADD COLUMN `pedido_id` INT(11) NULL DEFAULT NULL AFTER `cnpj_fornecedor`,
ADD INDEX `idx_pedido_id` (`pedido_id`) USING BTREE,
ADD CONSTRAINT `fk_nf_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON UPDATE CASCADE ON DELETE SET NULL; 