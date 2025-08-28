-- --------------------------------------------------------
-- Servidor:                     194.87.164.210
-- Versão do servidor:           10.6.22-MariaDB-cll-lve - MariaDB Server
-- OS do Servidor:               Linux
-- HeidiSQL Versão:              12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Copiando estrutura para tabela embalabag_site.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `SKU` varchar(255) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `texto` longtext DEFAULT NULL,
  `observacoes` longtext DEFAULT NULL,
  `condicoes` varchar(50) DEFAULT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `tamanho` varchar(50) DEFAULT NULL,
  `material` varchar(50) DEFAULT NULL,
  `categoria_id` varchar(50) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `valor` varchar(255) DEFAULT '0.00',
  `promocao` varchar(255) DEFAULT NULL,
  `promocao_tipo` enum('fixo','porcentagem') DEFAULT 'fixo',
  `porcentagem` int(11) DEFAULT 0,
  `promocao_de` timestamp NULL DEFAULT current_timestamp(),
  `promocao_ate` timestamp NULL DEFAULT current_timestamp(),
  `tags` longtext DEFAULT NULL,
  `destaque` bigint(255) DEFAULT 0,
  `status` enum('Publicado','Deletado','Rascunho') DEFAULT 'Publicado',
  `date_create` timestamp NULL DEFAULT current_timestamp(),
  `date_update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `dx_id_produto` (`id`) USING BTREE,
  KEY `idx_sku` (`SKU`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela embalabag_site.produtos_blacklist
CREATE TABLE IF NOT EXISTS `produtos_blacklist` (
  `id_empresa` int(11) DEFAULT NULL,
  `id_produto` int(11) DEFAULT NULL,
  `date_create` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Exportação de dados foi desmarcado.

CREATE TABLE `categorias` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nome` VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
	`slug` VARCHAR(255) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
	`nivel` BIGINT(20) NULL DEFAULT '0',
	`parent` BIGINT(20) NULL DEFAULT '0',
	`date_create` TIMESTAMP NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='latin1_swedish_ci' ENGINE=MyISAM AUTO_INCREMENT=1;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela embalabag_site.produtos_categorias
CREATE TABLE IF NOT EXISTS `produtos_categorias` (
  `id_produto` bigint(20) DEFAULT NULL,
  `id_categoria` bigint(20) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `nivel` int(11) DEFAULT NULL,
  `parent` bigint(20) DEFAULT NULL,
  KEY `dx_id_produto` (`id_produto`) USING BTREE,
  KEY `dx_id_categoria` (`id_categoria`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela embalabag_site.produtos_imagens
CREATE TABLE IF NOT EXISTS `produtos_imagens` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `id_produto` bigint(255) DEFAULT NULL,
  `imagem` longtext DEFAULT NULL,
  `imagem_original` longtext DEFAULT NULL,
  `watermark` longtext DEFAULT NULL,
  `thumbnail` longtext DEFAULT NULL,
  `nome` longtext DEFAULT NULL,
  `original` longtext DEFAULT NULL,
  `order` int(11) DEFAULT 0,
  `data` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `dx_id_produto` (`id_produto`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela embalabag_site.produtos_precos
CREATE TABLE IF NOT EXISTS `produtos_precos` (
  `id_empresa` int(11) DEFAULT NULL,
  `id_produto` int(11) DEFAULT NULL,
  `preco` varchar(255) DEFAULT NULL,
  `date_create` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela embalabag_site.produtos_variations
CREATE TABLE IF NOT EXISTS `produtos_variations` (
  `id_produto` bigint(255) DEFAULT NULL,
  `cor` varchar(255) DEFAULT NULL,
  `gerenciar_estoque` varchar(255) DEFAULT NULL,
  `estoque` int(11) DEFAULT NULL,
  `encomenda` varchar(255) DEFAULT 'no',
  `atraso` varchar(255) DEFAULT '0',
  `date_create` timestamp NULL DEFAULT current_timestamp(),
  KEY `dx_id_produto` (`id_produto`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Exportação de dados foi desmarcado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
