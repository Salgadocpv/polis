-- Tabela para Valores Fixos (para popular comboboxes)
CREATE TABLE IF NOT EXISTS valores_fixos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100) NOT NULL, -- Ex: 'departamento', 'status_projeto'
    valor VARCHAR(255) NOT NULL,
    UNIQUE (tipo, valor)
);