<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/auth.php';

$pdo = gamehub_pdo();

$user = gamehub_require_user('/login.php', $pdo);

$userId = (int)$user['id'];

$data = json_decode(
    file_get_contents('php://input'),
    true
);

$sessionId = trim(
    (string)($data['session'] ?? '')
);

if ($sessionId === '') {
    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'erro' => 'Sessão não informada.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Busca sessão
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        user_id,
        game_id,
        started_at,
        finished,
        final_score

    FROM game_sessions

    WHERE id = :id
      AND user_id = :user

    LIMIT 1
");

$stmt->execute([
    ':id' => $sessionId,
    ':user' => $userId
]);

$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    http_response_code(404);

    echo json_encode([
        'ok' => false,
        'erro' => 'Sessão inexistente.'
    ]);

    exit;
}

if ($session['finished']) {
    http_response_code(409);

    echo json_encode([
        'ok' => false,
        'erro' => 'Partida já encerrada.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Score atual
|--------------------------------------------------------------------------
*/

$currentScore = (int)$session['final_score'];

/*
|--------------------------------------------------------------------------
| Validação de tempo
|--------------------------------------------------------------------------
|
| No seu João Bird:
|
| velocidade do cano = 5 px/frame
| 60 FPS
|
| O primeiro ponto não acontece instantaneamente.
|
| Permitimos uma margem relativamente grande para não prejudicar
| usuário com lag.
|--------------------------------------------------------------------------
*/

$started = strtotime($session['started_at']);

$elapsed = max(
    0,
    time() - $started
);

/*
|--------------------------------------------------------------------------
| Limite plausível
|--------------------------------------------------------------------------
|
| Aproximadamente no máximo 1 ponto por segundo,
| com margem de segurança.
|--------------------------------------------------------------------------
*/

$maxAllowed =
    max(
        1,
        (int)floor($elapsed * 1.5) + 2
    );

$newScore = $currentScore + 1;

if ($newScore > $maxAllowed) {
    http_response_code(429);

    echo json_encode([
        'ok' => false,
        'erro' => 'Pontuação incompatível com o tempo da partida.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Incrementa no servidor
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE game_sessions

    SET final_score = final_score + 1

    WHERE id = :id
      AND user_id = :user
      AND finished = FALSE

    RETURNING final_score
");

$stmt->execute([
    ':id' => $sessionId,
    ':user' => $userId
]);

$score = (int)$stmt->fetchColumn();

echo json_encode([
    'ok' => true,
    'score' => $score
]);

exit;