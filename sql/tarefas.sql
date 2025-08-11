-- Tabela para Tarefas (associadas a projetos)
CREATE TABLE IF NOT EXISTS tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    responsavel_id INT,
    status VARCHAR(50),
    prioridade VARCHAR(50),
    data_inicio DATE,
    data_fim DATE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES colaboradores(id) ON DELETE SET NULL
);