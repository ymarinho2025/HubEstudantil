<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/auth.php';

$pdo = gamehub_pdo();

$user = gamehub_require_user(
    '/login.php',
    $pdo
);

$uid = (int)($user['id'] ?? 0);

if ($uid <= 0) {

    http_response_code(401);

    echo json_encode([
        'ok' => false,
        'erro' => 'Usuário não autenticado.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| JOÃO BIRD
|--------------------------------------------------------------------------
*/

$gameSlug = 'joao-bird';

$stmt = $pdo->prepare("
    SELECT id
    FROM games
    WHERE slug = :slug
      AND active = TRUE
    LIMIT 1
");

$stmt->execute([
    ':slug' => $gameSlug
]);

$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {

    http_response_code(404);

    echo json_encode([
        'ok' => false,
        'erro' => 'João Bird não encontrado na tabela games.'
    ]);

    exit;
}

$gameId = (int)$game['id'];

/*
|--------------------------------------------------------------------------
| GET
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $pdo->prepare("
        SELECT
            best_score,
            last_score,
            updated_at

        FROM game_scores

        WHERE user_id = :u
          AND game_id = :g

        LIMIT 1
    ");

    $stmt->execute([
        ':u' => $uid,
        ':g' => $gameId
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok' => true,

        'bestScore' =>
            (int)($row['best_score'] ?? 0),

        'lastScore' =>
            (int)($row['last_score'] ?? 0),

        'updatedAt' =>
            $row['updated_at'] ?? null
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'ok' => false,
        'erro' => 'Método não permitido.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| RECEBE SCORE
|--------------------------------------------------------------------------
*/

$input = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($input)) {
    $input = [];
}

if (!isset($input['score'])) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'erro' => 'Pontuação não recebida.'
    ]);

    exit;
}

$score = filter_var(
    $input['score'],
    FILTER_VALIDATE_INT
);

if (
    $score === false ||
    $score < 0 ||
    $score > 999999
) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'erro' => 'Pontuação inválida.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| SALVA PONTUAÇÃO PRINCIPAL
|--------------------------------------------------------------------------
|
| Sem transação compartilhada com histórico.
|
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO game_scores (
            user_id,
            game_id,
            best_score,
            last_score
        )

        VALUES (
            :u,
            :g,
            :s,
            :s
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
            last_score,
            updated_at
    ");

    $stmt->execute([
        ':u' => $uid,
        ':g' => $gameId,
        ':s' => $score
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Throwable $e) {

    error_log(
        'ERRO SCORE JOAO BIRD: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'erro' => $e->getMessage()
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| HISTÓRICO
|--------------------------------------------------------------------------
|
| Falha no histórico NÃO elimina o recorde.
|
*/

$historySaved = true;
$historyError = null;

try {

    $stmt = $pdo->prepare("
        INSERT INTO game_score_history (
            user_id,
            game_id,
            score
        )

        VALUES (
            :u,
            :g,
            :s
        )
    ");

    $stmt->execute([
        ':u' => $uid,
        ':g' => $gameId,
        ':s' => $score
    ]);

} catch (Throwable $e) {

    $historySaved = false;
    $historyError = $e->getMessage();

    error_log(
        'ERRO HISTORICO JOAO BIRD: ' .
        $historyError
    );
}

/*
|--------------------------------------------------------------------------
| RESPOSTA
|--------------------------------------------------------------------------
*/

echo json_encode([
    'ok' => true,

    'bestScore' =>
        (int)$row['best_score'],

    'lastScore' =>
        (int)$row['last_score'],

    'updatedAt' =>
        $row['updated_at'],

    'historySaved' =>
        $historySaved,

    'historyError' =>
        $historyError
]);

exit;