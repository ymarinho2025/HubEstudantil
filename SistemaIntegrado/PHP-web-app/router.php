<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = __DIR__;

$static = [
    '/css/' => $base . '/public/css/',
    '/js/'  => $base . '/public/js/',
    '/img/' => $base . '/public/img/',
];
foreach ($static as $prefix => $dir) {
    if (str_starts_with($uri, $prefix)) {
        $file = realpath($dir . substr($uri, strlen($prefix)));
        $root = realpath($dir);
        if ($file && $root && str_starts_with($file, $root) && is_file($file)) {
            return false;
        }
    }
}
if ($uri === '/' || $uri === '') $target = $base . '/api/index.php';
elseif (preg_match('#^/([^/]+\.php)$#', $uri, $m)) $target = $base . '/api/' . $m[1];
else {
    $candidate = $base . '/public' . $uri;
    if (is_file($candidate)) { return false; }
    http_response_code(404); echo "404 - Arquivo não encontrado"; exit;
}
if (!is_file($target)) { http_response_code(404); echo "404"; exit; }
chdir(dirname($target));
require $target;
