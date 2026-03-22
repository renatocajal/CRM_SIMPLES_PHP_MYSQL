-- Estrutura do Banco de Dados para novo_crm

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(50),
    email VARCHAR(255),
    nicho VARCHAR(255),
    cidade VARCHAR(255),
    status ENUM('Lead novo', 'Primeiro contato', 'Em negociação', 'Fechado', 'Perdido') DEFAULT 'Lead novo',
    origem VARCHAR(255),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_interacao DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco_padrao DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contato_produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contato_id INT NOT NULL,
    produto_id INT NOT NULL,
    preco_negociado DECIMAL(10, 2) NOT NULL,
    quantidade INT DEFAULT 1,
    status ENUM('Em negociação', 'Proposta enviada', 'Fechado', 'Perdido') DEFAULT 'Em negociação',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS interacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contato_id INT NOT NULL,
    tipo ENUM('primeiro_contato', 'follow_up') NOT NULL,
    descricao TEXT,
    data DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contato_id INT NOT NULL,
    descricao TEXT NOT NULL,
    data DATETIME NOT NULL,
    status ENUM('Pendente', 'Concluída') DEFAULT 'Pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    cor VARCHAR(10) DEFAULT '#000000',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contato_tags (
    contato_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (contato_id, tag_id),
    FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
