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

$endpoint = explode('.', $host)[0];

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require;options=endpoint=$endpoint";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;

} catch (PDOException $e) {
    die("Erro na conexão PostgreSQL: " . $e->getMessage());
}