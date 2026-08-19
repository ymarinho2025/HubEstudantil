<?php
require_once __DIR__ . '/load_env.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
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
function gamehub_ensure_nickname_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) return;

    $pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS nickname VARCHAR(15)');
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_nickname_unique_ci ON users (LOWER(nickname)) WHERE nickname IS NOT NULL");
    $done = true;
}

function gamehub_valid_nickname(string $nickname): bool
{
    $length = mb_strlen($nickname, 'UTF-8');
    if ($length < 3 || $length > 15) return false;

    // Somente letras Unicode, números e underscore. Bloqueia espaços, emojis e símbolos.
    return preg_match('/^[\\p{L}\\p{N}_]+$/u', $nickname) === 1;
}

function gamehub_require_nickname(array $user, string $redirect='/nickname.php'): array
{
    if (trim((string)($user['nickname'] ?? '')) === '') {
        header('Location: '.$redirect);
        exit;
    }
    return $user;
}

function gamehub_current_user(?PDO $pdo=null): ?array
{
    $token=$_COOKIE['auth_token']??'';
    if($token==='') return null;
    try{
        $d=JWT::decode($token,new Key(gamehub_jwt_secret(),'HS256'));
        $pdo=$pdo?:gamehub_pdo();
        gamehub_ensure_nickname_schema($pdo);
        $s=$pdo->prepare('SELECT id,name,nickname,email,roles FROM users WHERE id=:id LIMIT 1');
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
        gamehub_security_ensure_schema($pdo);
        $d=gamehub_security_client_data();
        $browser=isset($d['brands'])?json_encode($d['brands'],JSON_UNESCAPED_UNICODE):($d['userAgent']??null);
        $screen=is_array($d['screen']??null)?$d['screen']:[];
        $battery=is_array($d['battery']??null)?$d['battery']:[];
        $storage=is_array($d['storage']??null)?$d['storage']:[];
        $network=is_array($d['network']??null)?$d['network']:[];
        $stmt=$pdo->prepare('INSERT INTO user_logins
          (user_id,ip,user_agent,browser,platform,device,battery_percent,screen_width,screen_height,device_memory_gb,storage_quota_mb,storage_usage_mb,network_type,connection_effective_type,automation_detected,client_data)
          VALUES (:uid,:ip,:ua,:browser,:platform,:device,:battery,:sw,:sh,:ram,:quota,:usage,:network,:effective,:automation,CAST(:data AS JSONB))');
        $stmt->execute([
          ':uid'=>$uid, ':ip'=>gamehub_security_client_ip(),
          ':ua'=>mb_substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,2000,'UTF-8'),
          ':browser'=>gamehub_security_sanitize_scalar($browser,160),
          ':platform'=>gamehub_security_sanitize_scalar($d['platform']??null,120),
          ':device'=>!empty($d['mobile'])?'mobile/tablet':'desktop/unknown',
          ':battery'=>isset($battery['percent'])?(int)$battery['percent']:null,
          ':sw'=>isset($screen['width'])?(int)$screen['width']:null,
          ':sh'=>isset($screen['height'])?(int)$screen['height']:null,
          ':ram'=>isset($d['deviceMemoryGB'])?(float)$d['deviceMemoryGB']:null,
          ':quota'=>isset($storage['quotaMB'])?(int)$storage['quotaMB']:null,
          ':usage'=>isset($storage['usageMB'])?(int)$storage['usageMB']:null,
          ':network'=>gamehub_security_sanitize_scalar($network['type']??null,40),
          ':effective'=>gamehub_security_sanitize_scalar($network['effectiveType']??null,40),
          ':automation'=>!empty($d['webdriver']),
          ':data'=>json_encode($d,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?:'{}'
        ]);
        gamehub_security_record_event($pdo,'login_success',(string)($d['userAgent']??''),$uid);
    }catch(Throwable $e){}
}
