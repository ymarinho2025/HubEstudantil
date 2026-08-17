<?php
function hub_load_env(string $file): void
{
    if (!is_file($file)) return;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_starts_with($line, 'export ')) $line = trim(substr($line, 7));

        $pos = strpos($line, '=');
        if ($pos === false) continue;

        $name = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if ($name === '') continue;

        if (
            strlen($value) >= 2 &&
            (($value[0] === '"' && $value[strlen($value)-1] === '"') ||
             ($value[0] === "'" && $value[strlen($value)-1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false || getenv($name) === '') {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

foreach ([
    dirname(__DIR__) . '/.env',
    dirname(__DIR__) . '/GameHub/.env',
    dirname(__DIR__) . '/PHP-web-app/.env'
] as $envFile) {
    if (is_file($envFile)) hub_load_env($envFile);
}
