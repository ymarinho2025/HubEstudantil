<?php
require_once __DIR__ . '/../../../src/Controllers/login/auth.php';
header('Content-Type: application/json; charset=utf-8');
$gameSlug = 'flappy-bird';
$stmt = $pdo->prepare('SELECT id FROM games WHERE slug=:slug AND active=TRUE LIMIT 1');
$stmt->execute([':slug'=>$gameSlug]);
$game = $stmt->fetch();
if (!$game) { http_response_code(404); echo json_encode(['erro'=>'Jogo não encontrado']); exit; }
$gameId = (int)$game['id']; $uid = (int)$authUser['id'];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt=$pdo->prepare('SELECT best_score,last_score,updated_at FROM game_scores WHERE user_id=:u AND game_id=:g LIMIT 1');
    $stmt->execute([':u'=>$uid, ':g'=>$gameId]); $row=$stmt->fetch();
    echo json_encode(['ok'=>true,'bestScore'=>(int)($row['best_score']??0),'lastScore'=>(int)($row['last_score']??0),'updatedAt'=>$row['updated_at']??null]); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['erro'=>'Método não permitido']); exit; }
$input=json_decode(file_get_contents('php://input'), true) ?: [];
$score=$input['score'] ?? null;
if (!is_int($score) || $score < 0 || $score > 999999) { http_response_code(400); echo json_encode(['erro'=>'Pontuação inválida']); exit; }
try {
    $pdo->beginTransaction();
    $pdo->prepare('INSERT INTO game_score_history(user_id,game_id,score) VALUES(:u,:g,:s)')->execute([':u'=>$uid, ':g'=>$gameId, ':s'=>$score]);
    $stmt=$pdo->prepare("INSERT INTO game_scores(user_id,game_id,best_score,last_score) VALUES(:u,:g,:s,:s)
        ON CONFLICT(user_id, game_id) DO UPDATE SET last_score=EXCLUDED.last_score, best_score=GREATEST(game_scores.best_score, EXCLUDED.last_score)
        RETURNING best_score,last_score,updated_at");
    $stmt->execute([':u'=>$uid, ':g'=>$gameId, ':s'=>$score]); $row=$stmt->fetch();
    $pdo->commit();
    echo json_encode(['ok'=>true,'bestScore'=>(int)$row['best_score'],'lastScore'=>(int)$row['last_score'],'updatedAt'=>$row['updated_at']]);
} catch(Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); http_response_code(500); echo json_encode(['erro'=>'Erro ao salvar pontuação']); }
