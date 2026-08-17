<?php
if (isset($_GET['deslogar'])) {
    setcookie('auth_token', '', ['expires'=>time()-3600,'path'=>'/','httponly'=>true,'secure'=>false,'samesite'=>'Lax']);
    header('Location: /login.php'); exit;
}
