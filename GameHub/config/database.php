<?php
require_once __DIR__ . '/load_env.php';

function gamehub_database_config(): array
{
    $url = trim((string)getenv('DATABASE_URL'));
    if ($url === '') throw new RuntimeException('DATABASE_URL não configurada no .env.');

    $p = parse_url($url);
    if ($p === false || empty($p['host']) || empty($p['user']) || empty($p['path'])) {
        throw new RuntimeException('DATABASE_URL inválida.');
    }

    parse_str($p['query'] ?? '', $q);
    $host = $p['host'];
    return [
        'host'=>$host,
        'port'=>$p['port'] ?? 5432,
        'user'=>urldecode($p['user']),
        'pass'=>isset($p['pass']) ? urldecode($p['pass']) : '',
        'db'=>ltrim($p['path'],'/'),
        'sslmode'=>$q['sslmode'] ?? 'require',
        'endpoint'=>explode('.', $host)[0],
    ];
}

function gamehub_make_pdo(string $dsn, string $user, string $pass): PDO
{
    return new PDO($dsn,$user,$pass,[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
}

function gamehub_pdo(): PDO
{
    $c=gamehub_database_config();
    $base="pgsql:host={$c['host']};port={$c['port']};dbname={$c['db']};sslmode={$c['sslmode']}";

    try {
        return gamehub_make_pdo($base . ";options=endpoint={$c['endpoint']}", $c['user'], $c['pass']);
    } catch(PDOException $e) {
        if (str_contains($c['host'], '.neon.tech') && stripos($e->getMessage(),'Endpoint ID is not specified') !== false) {
            return gamehub_make_pdo($base, $c['user'], 'endpoint='.$c['endpoint'].'$'.$c['pass']);
        }
        throw $e;
    }
}
