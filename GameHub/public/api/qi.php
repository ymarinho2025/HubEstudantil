<?php
header('Content-Type: application/json; charset=utf-8');
$pdo=require __DIR__.'/../../src/db.php';
require_once __DIR__.'/../../config/auth.php';
$user=gamehub_require_user('/login.php',$pdo);

$pdo->exec("CREATE TABLE IF NOT EXISTS qi_points (
 user_id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
 points INT NOT NULL DEFAULT 0 CHECK(points>=0),
 updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS qi_history (
 id BIGSERIAL PRIMARY KEY,
 user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
 game_slug VARCHAR(60) NOT NULL,
 points INT NOT NULL DEFAULT 10 CHECK(points>0),
 created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
)");

if($_SERVER['REQUEST_METHOD']==='POST'){
  $data=json_decode(file_get_contents('php://input'),true) ?: [];
  $slug=preg_replace('/[^a-z0-9\-]/','',strtolower((string)($data['game']??'')));
  if($slug===''){http_response_code(422);echo json_encode(['ok'=>false]);exit;}
  $pdo->beginTransaction();
  try{
    $pdo->prepare("INSERT INTO qi_points(user_id,points) VALUES(:u,10)
      ON CONFLICT(user_id) DO UPDATE SET points=qi_points.points+10,updated_at=NOW()")
      ->execute([':u'=>(int)$user['id']]);
    $pdo->prepare("INSERT INTO qi_history(user_id,game_slug,points) VALUES(:u,:g,10)")
      ->execute([':u'=>(int)$user['id'],':g'=>$slug]);
    $st=$pdo->prepare("SELECT points FROM qi_points WHERE user_id=:u");
    $st->execute([':u'=>(int)$user['id']]);
    $points=(int)$st->fetchColumn();
    $pdo->commit();
    echo json_encode(['ok'=>true,'added'=>10,'points'=>$points]);
  }catch(Throwable $e){$pdo->rollBack();http_response_code(500);echo json_encode(['ok'=>false]);}
  exit;
}
$st=$pdo->prepare("SELECT points FROM qi_points WHERE user_id=:u");
$st->execute([':u'=>(int)$user['id']]);
echo json_encode(['ok'=>true,'points'=>(int)($st->fetchColumn()?:0)]);
