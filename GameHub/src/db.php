<?php
require_once dirname(__DIR__, 2) . '/config/database.php';

try {
    return hub_pdo();
} catch (Throwable $e) {
    throw new RuntimeException('Erro na conexão PostgreSQL: ' . $e->getMessage(), 0, $e);
}
