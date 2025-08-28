-- Criar tabela de relacionamento entre usuários e cargos
CREATE TABLE usuario_cargos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cargo_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_cargo (usuario_id, cargo_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=utf8mb4_unicode_ci; 