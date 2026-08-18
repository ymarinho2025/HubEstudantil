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
        game_id,
        final_score,
        finished

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
        'erro' => 'Sessão inválida.'
    ]);

    exit;
}

if ($session['finished']) {
    http_response_code(409);

    echo json_encode([
        'ok' => false,
        'erro' => 'Partida já finalizada.'
    ]);

    exit;
}

$gameId = (int)$session['game_id'];

$score = (int)$session['final_score'];

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Fecha sessão
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE game_sessions

        SET
            finished = TRUE,
            finished_at = NOW()

        WHERE id = :id
          AND user_id = :user
          AND finished = FALSE
    ");

    $stmt->execute([
        ':id' => $sessionId,
        ':user' => $userId
    ]);

    /*
    |--------------------------------------------------------------------------
    | Pontuação principal
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO game_scores (
            user_id,
            game_id,
            best_score,
            last_score
        )

        VALUES (
            :user,
            :game,
            :score,
            :score
        )

        ON CONFLICT (user_id, game_id)

        DO UPDATE SET

            last_score =
                EXCLUDED.last_score,

            best_score =
                GREATEST(
                    game_scores.best_score,
                    EXCLUDED.last_score
                ),

            updated_at =
                NOW()

        RETURNING
            best_score,
            last_score
    ");

    $stmt->execute([
        ':user' => $userId,
        ':game' => $gameId,
        ':score' => $score
    ]);

    $saved = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Histórico
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO game_score_history (
            user_id,
            game_id,
            score
        )

        VALUES (
            :user,
            :game,
            :score
        )
    ");

    $stmt->execute([
        ':user' => $userId,
        ':game' => $gameId,
        ':score' => $score
    ]);

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'score' => $score,
        'bestScore' =>
            (int)$saved['best_score'],
        'lastScore' =>
            (int)$saved['last_score']
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'erro' => $e->getMessage()
    ]);
}

exit;