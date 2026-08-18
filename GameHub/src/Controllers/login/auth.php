<?php
$pdo=require __DIR__.'/../../db.php';
require_once dirname(__DIR__,3).'/config/auth.php';
$authUser=gamehub_require_user('/login.php',$pdo);
$userName=$authUser['name'];
$role=(int)$authUser['roles'];
$key=gamehub_jwt_secret();
