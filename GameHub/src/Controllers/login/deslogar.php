<?php
require_once dirname(__DIR__,3).'/config/auth.php';
if(isset($_GET['deslogar'])){
    gamehub_clear_cookie();
    header('Location: /login.php');
    exit;
}
