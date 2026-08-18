<?php

require_once __DIR__ . '/config/auth.php';

use Firebase\JWT\JWT;

$pdo = hub_pdo();
$user = hub_require_user('/index.php', $pdo);

$target = $_GET['target'] ?? '';

$allowed = [
    'atividades' => getenv('ACTIVITIES_URL')
        ?: 'https://hubestudantil-atividades.vercel.app',

    'games' => getenv('GAMEHUB_URL')
        ?: 'https://hubestudantil-games.vercel.app',
];

if (!isset($allowed[$target])) {
    http_response_code(400);
    exit('Destino inválido.');
}

$secret = hub_jwt_key();

$now = time();

$payload = [
    'iss' => 'https://hubestudantil.vercel.app',
    'aud' => $target,
    'iat' => $now,
    'nbf' => $now,
    'exp' => $now + 60,
    'type' => 'hub_sso',

    'sub' => (string)($user['id'] ?? ''),
    'id' => (int)($user['id'] ?? 0),
    'email' => (string)($user['email'] ?? ''),
    'name' => (string)($user['name'] ?? ''),
    'roles' => (int)($user['roles'] ?? 1),
];

if ($payload['id'] <= 0) {
    http_response_code(401);
    exit('Usuário inválido.');
}

$token = JWT::encode(
    $payload,
    $secret,
    'HS256'
);

$callback = rtrim(
    $allowed[$target],
    '/'
) . '/sso-callback.php';

?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>HubEstudantil | Entrando</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f7fb;
            color: #172033;
            font-family: Arial, sans-serif;
        }

        .box {
            background: #fff;
            border: 1px solid #e4e7ec;
            border-radius: 18px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(16, 24, 40, .08);
        }

        .loader {
            width: 40px;
            height: 40px;
            margin: 22px auto;
            border: 4px solid #e4e7ec;
            border-top-color: #3157d5;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

<div class="box">

    <h2>HubEstudantil</h2>

    <div class="loader"></div>

    <p>Entrando automaticamente...</p>

    <form
        id="ssoForm"
        method="POST"
        action="<?= htmlspecialchars($callback, ENT_QUOTES, 'UTF-8') ?>"
    >

        <input
            type="hidden"
            name="sso_token"
            value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>"
        >

    </form>

</div>

<script>
document.getElementById('ssoForm').submit();
</script>

</body>
</html>