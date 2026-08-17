<?php
require_once __DIR__ . '/load_env.php';
require_once __DIR__ . '/database.php';

$autoloadCandidates = [
    dirname(__DIR__) . '/PHP-web-app/vendor/autoload.php',
    dirname(__DIR__) . '/GameHub/vendor/autoload.php',
];

$autoloadLoaded = false;
foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require_once $autoload;
        $autoloadLoaded = true;
        break;
    }
}

if (!$autoloadLoaded) {
    throw new RuntimeException('vendor/autoload.php não encontrado. Execute composer install ou mantenha a pasta vendor no projeto.');
}

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
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (str_contains($ip, ',')) $ip = trim(explode(',', $ip)[0]);

    try {
        $stmt = $pdo->prepare('INSERT INTO user_logins (user_id, ip) VALUES (:user_id, :ip)');
        $stmt->execute([
            ':user_id' => $userId,
            ':ip' => substr($ip, 0, 45)
        ]);
    } catch (Throwable $e) {
        // O login não deve falhar apenas porque a tabela de auditoria não existe.
    }
}
