<?php
$public = realpath(__DIR__ . '/../public');
if ($public === false) {
    // SGC keeps its PHP pages in the project root.
    $public = realpath(__DIR__ . '/..');
}
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uriPath = rawurldecode($uriPath);
if ($uriPath === '/' || $uriPath === '') $uriPath = '/index.php';
if (str_ends_with($uriPath, '/')) $uriPath .= 'index.php';
$candidate = realpath($public . '/' . ltrim($uriPath, '/'));
if ($candidate === false || !str_starts_with($candidate, $public . DIRECTORY_SEPARATOR) || !is_file($candidate)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo '404 - Página não encontrada';
    exit;
}
if (strtolower(pathinfo($candidate, PATHINFO_EXTENSION)) !== 'php') {
    http_response_code(404);
    exit;
}
$_SERVER['SCRIPT_FILENAME'] = $candidate;
$_SERVER['SCRIPT_NAME'] = $uriPath;
chdir(dirname($candidate));
require $candidate;
