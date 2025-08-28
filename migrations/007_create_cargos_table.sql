-- Criar tabela de cargos
CREATE TABLE cargos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    descricao TEXT NULL COLLATE 'utf8mb4_unicode_ci',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo' COLLATE 'utf8mb4_unicode_ci',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=utf8mb4_unicode_ci; 