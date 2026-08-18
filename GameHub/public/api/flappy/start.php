<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/auth.php';

$pdo = gamehub_pdo();

$user = gamehub_require_user('/login.php', $pdo);

$userId = (int)$user['id'];

/*
|--------------------------------------------------------------------------
| Localiza João Bird
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM games
    WHERE slug = 'joao-bird'
      AND active = TRUE
    LIMIT 1
");

$stmt->execute();

$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    http_response_code(404);

    echo json_encode([
        'ok' => false,
        'erro' => 'João Bird não encontrado.'
    ]);

    exit;
}

$gameId = (int)$game['id'];

/*
|--------------------------------------------------------------------------
| Gera UUID
|--------------------------------------------------------------------------
*/

function uuid_v4(): string
{
    $data = random_bytes(16);

    $data[6] = chr(
        (ord($data[6]) & 0x0f) | 0x40
    );

    $data[8] = chr(
        (ord($data[8]) & 0x3f) | 0x80
    );

    return vsprintf(
        '%s%s-%s-%s-%s-%s%s%s',
        str_split(bin2hex($data), 4)
    );
}

$sessionId = uuid_v4();

/*
|--------------------------------------------------------------------------
| Cria partida
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    INSERT INTO game_sessions (
        id,
        user_id,
        game_id,
        started_at,
        finished,
        final_score
    )
    VALUES (
        :id,
        :user,
        :game,
        NOW(),
        FALSE,
        0
    )
");

$stmt->execute([
    ':id' => $sessionId,
    ':user' => $userId,
    ':game' => $gameId
]);

echo json_encode([
    'ok' => true,
    'session' => $sessionId,
    'score' => 0
]);

exit;