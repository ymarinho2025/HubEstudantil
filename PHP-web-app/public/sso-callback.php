<?php

require_once __DIR__ . '/../config/auth.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$portalUrl = getenv('SSO_PORTAL_URL')
    ?: 'https://hubestudantil.vercel.app';

$ssoToken = $_POST['sso_token'] ?? '';

if ($ssoToken === '') {
    http_response_code(400);
    exit('Token SSO não recebido.');
}

try {

    $secret = hub_jwt_key();

    $payload = JWT::decode(
        $ssoToken,
        new Key($secret, 'HS256')
    );

    if (($payload->type ?? '') !== 'hub_sso') {
        throw new RuntimeException(
            'Tipo de token inválido: ' .
            ($payload->type ?? 'ausente')
        );
    }

    if (
        ($payload->iss ?? '') !==
        'https://hubestudantil.vercel.app'
    ) {
        throw new RuntimeException(
            'Emissor SSO inválido.'
        );
    }

    if (($payload->aud ?? '') !== 'atividades') {
        throw new RuntimeException(
            'Token não destinado ao sistema de atividades.'
        );
    }

    $userId = (int)($payload->id ?? $payload->sub ?? 0);

    if ($userId <= 0) {
        throw new RuntimeException(
            'ID do usuário ausente no token.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Busca o usuário no mesmo banco
    |--------------------------------------------------------------------------
    */

    $pdo = hub_pdo();

    $stmt = $pdo->prepare(
        'SELECT id, name, email, roles
         FROM users
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $userId
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new RuntimeException(
            'Usuário não encontrado no banco.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cria cookie usando a autenticação real do PHP-web-app
    |--------------------------------------------------------------------------
    */

    hub_issue_auth_cookie($user);

    header('Location: /home.php');
    exit;

} catch (Throwable $e) {

    http_response_code(500);

    echo '<h2>Erro SSO Atividades</h2>';

    echo '<pre>';

    echo htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    );

    echo '</pre>';

    exit;
}