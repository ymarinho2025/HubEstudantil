<?php

if (php_sapi_name() !== 'cli') {
    die("Acesso negado");
}

$pdo = require __DIR__ . '/db.php';

// USERS
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    roles INT DEFAULT 1
);
");

// MIDIA
$pdo->exec("
CREATE TABLE IF NOT EXISTS midia (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255),
    dia INT,
    mes INT,
    ano INT,
    imagem TEXT,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
");

// LOGINS
$pdo->exec("
CREATE TABLE IF NOT EXISTS user_logins (
    id SERIAL PRIMARY KEY,
    user_id INT,
    ip VARCHAR(45),
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
");

// SUPORTE
$pdo->exec("
CREATE TABLE IF NOT EXISTS suporte (
    id SERIAL PRIMARY KEY,
    user_id INT,
    assunto VARCHAR(255),
    mensagem TEXT,
    status VARCHAR(20) DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
");

echo "Banco PostgreSQL configurado com sucesso!";