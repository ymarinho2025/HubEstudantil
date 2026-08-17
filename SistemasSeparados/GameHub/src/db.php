<?php

$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    die("Erro: variável DATABASE_URL não configurada.");
}

$parsed = parse_url($databaseUrl);

$host = $parsed['host'];
$port = $parsed['port'] ?? 5432;
$user = $parsed['user'];
$pass = $parsed['pass'];
$db   = ltrim($parsed['path'], '/');

// IMPORTANTE: no Neon pooled, remova o "-pooler" do endpoint
$endpoint = explode('.', $host)[0];

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require;options=endpoint=$endpoint";

try {
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erro na conexão PostgreSQL: " . $e->getMessage());
}