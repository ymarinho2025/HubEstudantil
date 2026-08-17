<?php
require_once dirname(__DIR__, 4) . '/config/load_env.php';
$key = getenv('JWT_SECRET');
if (!$key || strlen($key) < 32) {
    throw new RuntimeException('JWT_SECRET ausente ou inválido.');
}
