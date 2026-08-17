<?php
require_once __DIR__ . '/config/database.php';
header('Content-Type: text/html; charset=utf-8');
try {
    $cfg = hub_parse_database_url();
    $pdo = hub_pdo();
    $row = $pdo->query("SELECT current_database() banco, current_user usuario")->fetch();
    echo "<h1>✅ Conexão com o Neon funcionando</h1>";
    echo "<p><b>Host:</b> ".htmlspecialchars($cfg['host'])."</p>";
    echo "<p><b>Endpoint:</b> ".htmlspecialchars($cfg['endpoint'])."</p>";
    echo "<p><b>Banco:</b> ".htmlspecialchars($row['banco'])."</p>";
    echo "<p><b>Usuário:</b> ".htmlspecialchars($row['usuario'])."</p>";
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h1>❌ Falha na conexão</h1><pre>".htmlspecialchars($e->getMessage())."</pre>";
}
