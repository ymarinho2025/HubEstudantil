<?php
$pdo=require __DIR__.'/../../db.php';
require_once dirname(__DIR__,3).'/config/auth.php';
$loginErro='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email']??'');
    $senha=$_POST['password']??'';
    $s=$pdo->prepare('SELECT id,name,email,password,roles FROM users WHERE LOWER(email)=LOWER(:e) LIMIT 1');
    $s->execute([':e'=>$email]);
    $u=$s->fetch();
    if($u && gamehub_verify_password($pdo,$u,$senha)){
        gamehub_record_login($pdo,(int)$u['id']);
        gamehub_issue_cookie($u);
        header('Location: /home.php'); exit;
    }
    $loginErro='Email ou senha incorretos.';
}elseif(gamehub_current_user($pdo)){
    header('Location: /home.php'); exit;
}
