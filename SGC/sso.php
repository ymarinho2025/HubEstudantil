<?php

require_once __DIR__ . '/config/auth.php';

$user = require_auth();

$target = $_GET['target'] ?? '';

$allowed = [
    'atividades' => getenv('ACTIVITIES_URL') ?: 'https://hubestudantil-atividades.vercel.app',
    'games'      => getenv('GAMEHUB_URL') ?: 'https://hubestudantil-games.vercel.app',
];

if (!isset($allowed[$target])) {
    http_response_code(400);
    exit('Destino inválido.');
}

$secret = getenv('JWT_SECRET');

if (!$secret) {
    http_response_code(500);
    exit('JWT_SECRET não configurado.');
}

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

$now = time();

$payload = [
    'iss' => 'https://hubestudantil.vercel.app',
    'iat' => $now,
    'exp' => $now + 60,
    'sub' => (string)($user['id'] ?? ''),
    'email' => $user['email'] ?? '',
    'username' => $user['username'] ?? ($user['name'] ?? ''),
    'type' => 'sso'
];

$token = JWT::encode($payload, $secret, 'HS256');

$callback = rtrim($allowed[$target], '/') . '/sso-callback.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Entrando...</title>
</head>
<body>

<form id="ssoForm" method="POST"
      action="<?= htmlspecialchars($callback, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden"
           name="sso_token"
           value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
</form>

<script>
document.getElementById('ssoForm').submit();
</script>

</body>
</html>