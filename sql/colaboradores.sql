-- Tabela para Colaboradores
CREATE TABLE IF NOT EXISTS colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    cargo VARCHAR(255),
    departamento VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    data_contratacao DATE,
    
    nivel_acesso VARCHAR(50),
    foto_url VARCHAR(255),
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);