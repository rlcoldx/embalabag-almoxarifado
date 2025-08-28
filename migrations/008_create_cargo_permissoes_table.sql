-- Criar tabela de relacionamento entre cargos e permissões
CREATE TABLE cargo_permissoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cargo_id INT NOT NULL,
    permissao_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON DELETE CASCADE,
    FOREIGN KEY (permissao_id) REFERENCES permissoes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cargo_permissao (cargo_id, permissao_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=utf8mb4_unicode_ci; 