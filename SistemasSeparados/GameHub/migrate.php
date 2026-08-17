<?php
if (php_sapi_name() !== 'cli') die('Acesso negado');
$pdo = require __DIR__ . '/src/db.php';
$sql = file_get_contents(__DIR__ . '/database/schema.sql');
$pdo->exec($sql);
echo "Hub de jogos migrado com sucesso!\n";
