-- Migration: 003_create_usuario_permissoes_table.sql
-- Descrição: Criação da tabela de relacionamento entre usuários e permissões

CREATE TABLE IF NOT EXISTS `usuario_permissoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL,
  `concedido_por` int(11) DEFAULT NULL,
  `concedido_em` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_permissao` (`usuario_id`, `permissao_id`),
  KEY `fk_usuario_permissoes_usuario` (`usuario_id`),
  KEY `fk_usuario_permissoes_permissao` (`permissao_id`),
  KEY `fk_usuario_permissoes_concedido_por` (`concedido_por`),
  CONSTRAINT `fk_usuario_permissoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuario_permissoes_permissao` FOREIGN KEY (`permissao_id`) REFERENCES `permissoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuario_permissoes_concedido_por` FOREIGN KEY (`concedido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conceder todas as permissões ao administrador
INSERT INTO `usuario_permissoes` (`usuario_id`, `permissao_id`, `concedido_por`) 
SELECT 1, id, 1 FROM `permissoes`; 