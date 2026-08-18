<?php

require_once __DIR__ . '/../../../src/Controllers/login/auth.php';

header('Content-Type: application/json; charset=utf-8');

try {

    /*
    |--------------------------------------------------------------------------
    | Usuário autenticado
    |--------------------------------------------------------------------------
    */

    $uid = (int)($authUser['id'] ?? 0);

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
    | Jogo principal
    |--------------------------------------------------------------------------
    */

    $gameSlug = 'joao-bird';

    $stmt = $pdo->prepare(
        'SELECT id
         FROM games
         WHERE slug = :slug
         LIMIT 1'
    );

    $stmt->execute([
        ':slug' => $gameSlug
    ]);

    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Se João Bird ainda não existir no banco, cria
    |--------------------------------------------------------------------------
    */

    if (!$game) {

        $stmt = $pdo->prepare(
            "INSERT INTO games
            (
                slug,
                title,
                description,
                cover_url,
                game_url,
                active
            )
            VALUES
            (
                :slug,
                :title,
                :description,
                :cover,
                :url,
                TRUE
            )
            ON CONFLICT (slug)
            DO UPDATE SET
                active = TRUE
            RETURNING id"
        );

        $stmt->execute([
            ':slug' => 'joao-bird',
            ':title' => 'João Bird',
            ':description' =>
                'Jogo educacional principal do GameHub.',
            ':cover' => '/games/JoaoBird/bg.png',
            ':url' => '/play.php?game=joao-bird'
        ]);

        $game = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$game) {
        throw new RuntimeException(
            'Não foi possível localizar ou criar João Bird.'
        );
    }

    $gameId = (int)$game['id'];

    /*
    |--------------------------------------------------------------------------
    | CONSULTA
    |--------------------------------------------------------------------------
    */

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $stmt = $pdo->prepare(
            'SELECT
                best_score,
                last_score,
                updated_at
             FROM game_scores
             WHERE user_id = :user
               AND game_id = :game
             LIMIT 1'
        );

        $stmt->execute([
            ':user' => $uid,
            ':game' => $gameId
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
    | Somente POST para gravar
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
    | RECEBE PONTUAÇÃO
    |--------------------------------------------------------------------------
    */

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        $input = [];
    }

    /*
    |--------------------------------------------------------------------------
    | Converte para inteiro
    |--------------------------------------------------------------------------
    |
    | Na versão antiga havia:
    |
    | is_int($score)
    |
    | Isso pode ser excessivamente rígido dependendo de como
    | o JSON chega.
    |
    */

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
    | GRAVA
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Histórico
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'INSERT INTO game_score_history
        (
            user_id,
            game_id,
            score
        )
        VALUES
        (
            :user,
            :game,
            :score
        )'
    );

    $stmt->execute([
        ':user' => $uid,
        ':game' => $gameId,
        ':score' => $score
    ]);

    /*
    |--------------------------------------------------------------------------
    | Melhor pontuação e última pontuação
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'INSERT INTO game_scores
        (
            user_id,
            game_id,
            best_score,
            last_score
        )
        VALUES
        (
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
            last_score,
            updated_at'
    );

    $stmt->execute([
        ':user' => $uid,
        ':game' => $gameId,
        ':score' => $score
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();

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
            $row['updated_at']
    ]);

    exit;

} catch (Throwable $e) {

    if (
        isset($pdo) &&
        $pdo instanceof PDO &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    error_log(
        'Erro pontuação João Bird: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'ok' => false,

        /*
         * TEMPORÁRIO para diagnóstico.
         * Depois podemos retirar o detalhe.
         */
        'erro' => $e->getMessage()
    ]);

    exit;
}