-- ===== ATUALIZAÇÕES PARA INTEGRAÇÃO DE USUÁRIOS E COLABORADORES =====
-- Script para adicionar campos necessários para o sistema de login automático

-- Adicionar campo usuario na tabela colaboradores
ALTER TABLE colaboradores ADD COLUMN usuario VARCHAR(50) UNIQUE AFTER email;

-- Adicionar campos na tabela usuarios para controlar primeira senha
ALTER TABLE usuarios ADD COLUMN colaborador_id INT AFTER id;
ALTER TABLE usuarios ADD COLUMN primeira_senha BOOLEAN DEFAULT TRUE AFTER password_hash;
ALTER TABLE usuarios ADD COLUMN senha_temporaria BOOLEAN DEFAULT FALSE AFTER primeira_senha;
ALTER TABLE usuarios ADD COLUMN ultimo_login TIMESTAMP NULL AFTER data_cadastro;

-- Adicionar chave estrangeira para relacionar usuarios com colaboradores
ALTER TABLE usuarios ADD FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE;

-- Índices para melhor performance
CREATE INDEX idx_usuarios_colaborador ON usuarios(colaborador_id);
CREATE INDEX idx_usuarios_primeira_senha ON usuarios(primeira_senha);
CREATE INDEX idx_colaboradores_usuario ON colaboradores(usuario);