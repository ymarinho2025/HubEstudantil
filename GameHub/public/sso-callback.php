<?php

require_once __DIR__ . '/../config/auth.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
|--------------------------------------------------------------------------
| PORTAL PRINCIPAL
|--------------------------------------------------------------------------
*/

$portalUrl = getenv('SSO_PORTAL_URL')
    ?: 'https://hubestudantil.vercel.app';

/*
|--------------------------------------------------------------------------
| RECEBE TOKEN SSO
|--------------------------------------------------------------------------
*/

$ssoToken = $_POST['sso_token'] ?? '';

if ($ssoToken === '') {
    http_response_code(400);
    exit('Token SSO não recebido.');
}

try {

    /*
    |--------------------------------------------------------------------------
    | MESMA CHAVE JWT DO GAMEHUB
    |--------------------------------------------------------------------------
    */

    $secret = gamehub_jwt_secret();

    /*
    |--------------------------------------------------------------------------
    | DECODIFICA TOKEN
    |--------------------------------------------------------------------------
    */

    $payload = JWT::decode(
        $ssoToken,
        new Key($secret, 'HS256')
    );

    /*
    |--------------------------------------------------------------------------
    | VALIDA TIPO
    |--------------------------------------------------------------------------
    */

    if (($payload->type ?? '') !== 'hub_sso') {
        throw new RuntimeException(
            'Tipo de token inválido: ' .
            ($payload->type ?? 'ausente')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDA EMISSOR
    |--------------------------------------------------------------------------
    */

    if (
        ($payload->iss ?? '') !==
        'https://hubestudantil.vercel.app'
    ) {
        throw new RuntimeException(
            'Emissor SSO inválido.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDA DESTINO
    |--------------------------------------------------------------------------
    */

    if (($payload->aud ?? '') !== 'games') {
        throw new RuntimeException(
            'Token não destinado ao GameHub.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ID DO USUÁRIO
    |--------------------------------------------------------------------------
    */

    $userId = (int)(
        $payload->id
        ?? $payload->sub
        ?? 0
    );

    if ($userId <= 0) {
        throw new RuntimeException(
            'ID do usuário ausente no token.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCA USUÁRIO NO BANCO
    |--------------------------------------------------------------------------
    */

    $pdo = gamehub_pdo();

    $stmt = $pdo->prepare(
        'SELECT id, name, email, roles
         FROM users
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $userId
    ]);

    $user = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$user) {
        throw new RuntimeException(
            'Usuário não encontrado no banco.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CRIA COOKIE USANDO O PRÓPRIO GAMEHUB
    |--------------------------------------------------------------------------
    */

    gamehub_issue_cookie($user);

    /*
    |--------------------------------------------------------------------------
    | ENTRA NO GAMEHUB
    |--------------------------------------------------------------------------
    */

    header('Location: /home.php');
    exit;

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | DIAGNÓSTICO
    |--------------------------------------------------------------------------
    |
    | Por enquanto mostramos o erro.
    | Depois que tudo estiver funcionando,
    | podemos voltar a redirecionar ao portal.
    |
    */

    http_response_code(500);

    echo '<!doctype html>';
    echo '<html lang="pt-BR">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<title>Erro SSO GameHub</title>';
    echo '</head>';
    echo '<body>';

    echo '<h2>Erro SSO GameHub</h2>';

    echo '<pre>';

    echo htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    );

    echo '</pre>';

    echo '<p>';
    echo '<a href="' .
        htmlspecialchars(
            $portalUrl,
            ENT_QUOTES,
            'UTF-8'
        ) .
        '">Voltar ao HubEstudantil</a>';
    echo '</p>';

    echo '</body>';
    echo '</html>';

    exit;
}