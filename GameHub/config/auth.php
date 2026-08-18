<?php
require_once __DIR__ . '/load_env.php';
require_once __DIR__ . '/database.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function gamehub_jwt_secret(): string
{
    $key=(string)getenv('JWT_SECRET');
    if (strlen($key)<32) throw new RuntimeException('JWT_SECRET ausente ou muito curto.');
    return $key;
}
function gamehub_cookie_options(int $expires): array
{
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    return ['expires'=>$expires,'path'=>'/','httponly'=>true,'secure'=>$secure,'samesite'=>'Lax'];
}
function gamehub_issue_cookie(array $u): string
{
    $now=time();
    $jwt=JWT::encode([
        'iat'=>$now,'exp'=>$now+86400,'id'=>(int)$u['id'],'roles'=>(int)($u['roles']??1),
        'name'=>(string)$u['name'],'email'=>(string)$u['email']
    ],gamehub_jwt_secret(),'HS256');
    setcookie('auth_token',$jwt,gamehub_cookie_options($now+86400));
    $_COOKIE['auth_token']=$jwt;
    return $jwt;
}
function gamehub_clear_cookie(): void
{
    setcookie('auth_token','',gamehub_cookie_options(time()-3600));
    unset($_COOKIE['auth_token']);
}
function gamehub_current_user(?PDO $pdo=null): ?array
{
    $token=$_COOKIE['auth_token']??'';
    if($token==='') return null;
    try{
        $d=JWT::decode($token,new Key(gamehub_jwt_secret(),'HS256'));
        $pdo=$pdo?:gamehub_pdo();
        $s=$pdo->prepare('SELECT id,name,email,roles FROM users WHERE id=:id LIMIT 1');
        $s->execute([':id'=>(int)$d->id]);
        return $s->fetch() ?: null;
    }catch(Throwable $e){
        gamehub_clear_cookie();
        return null;
    }
}
function gamehub_require_user(string $login='/login.php', ?PDO $pdo=null): array
{
    $u=gamehub_current_user($pdo);
    if(!$u){ header('Location: '.$login); exit; }
    return $u;
}
function gamehub_verify_password(PDO $pdo,array $u,string $password): bool
{
    $stored=(string)$u['password'];
    if(password_verify($password,$stored)) return true;
    if($stored!=='' && hash_equals($stored,hash('sha256',$password))){
        $pdo->prepare('UPDATE users SET password=:p WHERE id=:id')
            ->execute([':p'=>password_hash($password,PASSWORD_DEFAULT),':id'=>(int)$u['id']]);
        return true;
    }
    return false;
}
function gamehub_record_login(PDO $pdo,int $uid): void
{
    try{
        $ip=$_SERVER['REMOTE_ADDR']??'0.0.0.0';
        $pdo->prepare('INSERT INTO user_logins(user_id,ip) VALUES(:u,:ip)')
            ->execute([':u'=>$uid,':ip'=>substr($ip,0,45)]);
    }catch(Throwable $e){}
}
