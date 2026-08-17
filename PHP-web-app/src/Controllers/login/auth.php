<?php
$pdo = require __DIR__ . '/../db.php';
require_once dirname(__DIR__, 4) . '/config/auth.php';

$authUser = hub_require_user('/login.php', $pdo);
$userName = $authUser['name'];
$role = (int)$authUser['roles'];
$key = hub_jwt_key();
