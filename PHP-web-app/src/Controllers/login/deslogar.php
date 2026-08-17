<?php
 
if (isset($_GET['deslogar'])) {
    // Invalida o cookie sobrescrevendo com expiração no passado
    setcookie("auth_token", "", [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'secure'   => false,
        'samesite' => 'Lax'
    ]);
 
    unset($_COOKIE['auth_token']);
 
    // Redireciona para o login após deslogar
    header('Location: /login.php');
    exit();
}
 