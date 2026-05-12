-- =============================================
-- SISTEMA DE FINANÇAS PESSOAIS
-- Execute este arquivo para criar o banco de dados
-- =============================================

CREATE DATABASE IF NOT EXISTS financas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE financas;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa', 'ambos') DEFAULT 'ambos',
    cor VARCHAR(7) DEFAULT '#6366f1',
    icone VARCHAR(50) DEFAULT 'tag',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Transações
CREATE TABLE IF NOT EXISTS transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    categoria_id INT,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE NULL,
    status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
    parcela_atual INT DEFAULT 1,
    total_parcelas INT DEFAULT 1,
    grupo_parcelamento VARCHAR(36) NULL,
    observacao TEXT NULL,
    recorrente TINYINT(1) DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Categorias padrão
INSERT INTO categorias (nome, tipo, cor, icone) VALUES
('Salário', 'receita', '#10b981', 'briefcase'),
('Freelance', 'receita', '#06b6d4', 'laptop'),
('Investimentos', 'receita', '#8b5cf6', 'trending-up'),
('Outros (Receita)', 'receita', '#f59e0b', 'plus-circle'),
('Moradia', 'despesa', '#ef4444', 'home'),
('Alimentação', 'despesa', '#f97316', 'shopping-cart'),
('Transporte', 'despesa', '#eab308', 'car'),
('Saúde', 'despesa', '#ec4899', 'heart'),
('Educação', 'despesa', '#6366f1', 'book'),
('Lazer', 'despesa', '#14b8a6', 'smile'),
('Roupas', 'despesa', '#a855f7', 'tag'),
('Assinaturas', 'despesa', '#64748b', 'repeat'),
('Cartão de Crédito', 'despesa', '#dc2626', 'credit-card'),
('Outros (Despesa)', 'despesa', '#78716c', 'more-horizontal');
