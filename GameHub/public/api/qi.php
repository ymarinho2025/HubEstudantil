<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/auth.php';

$pdo = gamehub_pdo();
$user = gamehub_require_user('/login.php', $pdo);

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
| GARANTE TABELA PRINCIPAL
|--------------------------------------------------------------------------
*/

$pdo->exec("
    CREATE TABLE IF NOT EXISTS qi_points (
        user_id INT PRIMARY KEY
            REFERENCES users(id)
            ON DELETE CASCADE,

        points INT NOT NULL
            DEFAULT 0,

        updated_at TIMESTAMPTZ
            NOT NULL
            DEFAULT NOW()
    )
");

/*
|--------------------------------------------------------------------------
| GET - CONSULTA QI
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $pdo->prepare("
        SELECT points
        FROM qi_points
        WHERE user_id = :u
        LIMIT 1
    ");

    $stmt->execute([
        ':u' => $uid
    ]);

    $points = $stmt->fetchColumn();

    echo json_encode([
        'ok' => true,
        'points' => (int)($points ?: 0)
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| SOMENTE POST
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
| RECEBE JOGO
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($data)) {
    $data = [];
}

$slug = preg_replace(
    '/[^a-z0-9\-]/',
    '',
    strtolower(
        (string)($data['game'] ?? '')
    )
);

if ($slug === '') {

    http_response_code(422);

    echo json_encode([
        'ok' => false,
        'erro' => 'Jogo não informado.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| SALVA PRIMEIRO O QI
|--------------------------------------------------------------------------
|
| IMPORTANTE:
|
| Não usamos transação aqui.
|
| A pontuação principal não pode depender
| da gravação do histórico.
|
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO qi_points (
            user_id,
            points,
            updated_at
        )
        VALUES (
            :u,
            10,
            NOW()
        )

        ON CONFLICT (user_id)

        DO UPDATE SET

            points =
                qi_points.points + 10,

            updated_at =
                NOW()

        RETURNING points
    ");

    $stmt->execute([
        ':u' => $uid
    ]);

    $points = (int)$stmt->fetchColumn();

} catch (Throwable $e) {

    error_log(
        'ERRO QI PRINCIPAL: ' .
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
| Se o histórico falhar, NÃO desfaz os 10 QI.
|
*/

$historySaved = true;
$historyError = null;

try {

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS qi_history (
            id BIGSERIAL PRIMARY KEY,

            user_id INT NOT NULL
                REFERENCES users(id)
                ON DELETE CASCADE,

            game_slug VARCHAR(60)
                NOT NULL,

            points INT NOT NULL
                DEFAULT 10,

            created_at TIMESTAMPTZ
                NOT NULL
                DEFAULT NOW()
        )
    ");

    $stmt = $pdo->prepare("
        INSERT INTO qi_history (
            user_id,
            game_slug,
            points
        )
        VALUES (
            :u,
            :g,
            10
        )
    ");

    $stmt->execute([
        ':u' => $uid,
        ':g' => $slug
    ]);

} catch (Throwable $e) {

    $historySaved = false;
    $historyError = $e->getMessage();

    error_log(
        'ERRO HISTORICO QI: ' .
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
    'added' => 10,
    'points' => $points,
    'historySaved' => $historySaved,
    'historyError' => $historyError
]);

exit;