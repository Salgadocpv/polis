-- Tabela para Projetos
CREATE TABLE IF NOT EXISTS projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cliente_id INT,
    responsavel VARCHAR(255),
    status VARCHAR(50),
    data_inicio DATE,
    data_conclusao_prevista DATE,
    orcamento DECIMAL(15, 2),
    descricao TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);