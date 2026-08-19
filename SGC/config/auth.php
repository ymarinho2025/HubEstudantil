<?php
require_once __DIR__ . '/load_env.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function hub_jwt_key(): string
{
    $key = getenv('JWT_SECRET');
    if (!$key || strlen($key) < 32) {
        throw new RuntimeException('JWT_SECRET ausente ou muito curto no .env.');
    }
    return $key;
}

function hub_cookie_options(int $expires): array
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $secureEnv = strtolower((string)(getenv('COOKIE_SECURE') ?: ''));
    if (in_array($secureEnv, ['1','true','yes'], true)) $https = true;
    if (in_array($secureEnv, ['0','false','no'], true)) $https = false;

    $options = [
        'expires'  => $expires,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $https,
        'samesite' => 'Lax',
    ];

    $domain = trim((string)(getenv('COOKIE_DOMAIN') ?: ''));
    if ($domain !== '') {
        $options['domain'] = $domain;
    }

    return $options;
}

function hub_issue_auth_cookie(array $user): string
{
    $now = time();
    $ttl = 60 * 60 * 24; // 24 horas
    $payload = [
        'iat'   => $now,
        'exp'   => $now + $ttl,
        'id'    => (int)$user['id'],
        'roles' => (int)($user['roles'] ?? 1),
        'email' => (string)($user['email'] ?? ''),
        'name'  => (string)($user['name'] ?? ''),
    ];

    $jwt = JWT::encode($payload, hub_jwt_key(), 'HS256');
    setcookie('auth_token', $jwt, hub_cookie_options($now + $ttl));
    $_COOKIE['auth_token'] = $jwt;
    return $jwt;
}

function hub_clear_auth_cookie(): void
{
    setcookie('auth_token', '', hub_cookie_options(time() - 3600));
    unset($_COOKIE['auth_token']);
}

function hub_current_user(?PDO $pdo = null): ?array
{
    $token = $_COOKIE['auth_token'] ?? '';
    if ($token === '') return null;

    try {
        $decoded = JWT::decode($token, new Key(hub_jwt_key(), 'HS256'));
        $pdo = $pdo ?: hub_pdo();
        $stmt = $pdo->prepare('SELECT id, name, email, roles FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)$decoded->id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            hub_clear_auth_cookie();
            return null;
        }
        return $user;
    } catch (Throwable $e) {
        hub_clear_auth_cookie();
        return null;
    }
}

function hub_require_user(string $loginUrl, ?PDO $pdo = null): array
{
    $user = hub_current_user($pdo);
    if (!$user) {
        header('Location: ' . $loginUrl);
        exit;
    }
    return $user;
}

function hub_verify_password_and_upgrade(PDO $pdo, array $user, string $password): bool
{
    $stored = (string)($user['password'] ?? '');

    if ($stored !== '' && password_verify($password, $stored)) {
        if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password = :password WHERE id = :id')
                ->execute([':password' => $newHash, ':id' => (int)$user['id']]);
        }
        return true;
    }

    // Compatibilidade com contas antigas do PHP-web-app em SHA-256.
    if ($stored !== '' && hash_equals($stored, hash('sha256', $password))) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = :password WHERE id = :id')
            ->execute([':password' => $newHash, ':id' => (int)$user['id']]);
        return true;
    }

    return false;
}

function hub_record_login(PDO $pdo, int $userId): void
{
    try {
        hub_security_ensure_schema($pdo);
        $d = hub_security_client_data();
        $browser = isset($d['brands']) ? json_encode($d['brands'], JSON_UNESCAPED_UNICODE) : ($d['userAgent'] ?? null);
        $screen = is_array($d['screen'] ?? null) ? $d['screen'] : [];
        $battery = is_array($d['battery'] ?? null) ? $d['battery'] : [];
        $storage = is_array($d['storage'] ?? null) ? $d['storage'] : [];
        $network = is_array($d['network'] ?? null) ? $d['network'] : [];
        $stmt = $pdo->prepare('INSERT INTO user_logins
          (user_id,ip,user_agent,browser,platform,device,battery_percent,screen_width,screen_height,device_memory_gb,storage_quota_mb,storage_usage_mb,network_type,connection_effective_type,automation_detected,client_data)
          VALUES (:uid,:ip,:ua,:browser,:platform,:device,:battery,:sw,:sh,:ram,:quota,:usage,:network,:effective,:automation,CAST(:data AS JSONB))');
        $stmt->execute([
          ':uid'=>$userId,
          ':ip'=>hub_security_client_ip(),
          ':ua'=>mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''),0,2000,'UTF-8'),
          ':browser'=>hub_security_sanitize_scalar($browser,160),
          ':platform'=>hub_security_sanitize_scalar($d['platform'] ?? null,120),
          ':device'=>!empty($d['mobile']) ? 'mobile/tablet' : 'desktop/unknown',
          ':battery'=>isset($battery['percent']) ? (int)$battery['percent'] : null,
          ':sw'=>isset($screen['width']) ? (int)$screen['width'] : null,
          ':sh'=>isset($screen['height']) ? (int)$screen['height'] : null,
          ':ram'=>isset($d['deviceMemoryGB']) ? (float)$d['deviceMemoryGB'] : null,
          ':quota'=>isset($storage['quotaMB']) ? (int)$storage['quotaMB'] : null,
          ':usage'=>isset($storage['usageMB']) ? (int)$storage['usageMB'] : null,
          ':network'=>hub_security_sanitize_scalar($network['type'] ?? null,40),
          ':effective'=>hub_security_sanitize_scalar($network['effectiveType'] ?? null,40),
          ':automation'=>!empty($d['webdriver']),
          ':data'=>json_encode($d, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?: '{}'
        ]);
        hub_security_record_event($pdo,'login_success',(string)($d['userAgent'] ?? ''),$userId);
    } catch (Throwable $e) {}
}
