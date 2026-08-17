<?php
require_once __DIR__ . '/load_env.php';

function hub_database_url(): string
{
    $url = getenv('DATABASE_URL');
    if (!$url) {
        throw new RuntimeException('DATABASE_URL não configurada no arquivo .env.');
    }
    return trim($url);
}

function hub_parse_database_url(): array
{
    $parsed = parse_url(hub_database_url());

    if ($parsed === false || empty($parsed['host']) || empty($parsed['user']) || empty($parsed['path'])) {
        throw new RuntimeException('DATABASE_URL inválida.');
    }

    $host = $parsed['host'];
    $port = $parsed['port'] ?? 5432;
    $user = urldecode($parsed['user']);
    $pass = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
    $db = ltrim($parsed['path'], '/');

    parse_str($parsed['query'] ?? '', $query);
    $sslmode = $query['sslmode'] ?? 'require';

    $endpoint = explode('.', $host)[0];
    if (!empty($query['options']) && str_starts_with($query['options'], 'endpoint=')) {
        $endpoint = substr($query['options'], strlen('endpoint='));
    }

    return compact('host','port','user','pass','db','sslmode','endpoint');
}

function hub_make_pdo(string $dsn, string $user, string $password): PDO
{
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function hub_pdo(): PDO
{
    $c = hub_parse_database_url();

    $baseDsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['db']};sslmode={$c['sslmode']}";
    $endpointDsn = $baseDsn . ";options=endpoint={$c['endpoint']}";

    try {
        return hub_make_pdo($endpointDsn, $c['user'], $c['pass']);
    } catch (PDOException $e) {
        if (
            str_contains($c['host'], '.neon.tech') &&
            stripos($e->getMessage(), 'Endpoint ID is not specified') !== false
        ) {
            // Compatibilidade Neon com libpq antigo sem SNI.
            $passwordWithEndpoint = 'endpoint=' . $c['endpoint'] . '$' . $c['pass'];
            return hub_make_pdo($baseDsn, $c['user'], $passwordWithEndpoint);
        }
        throw $e;
    }
}
