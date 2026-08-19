<?php
$pdo=require __DIR__.'/../../db.php';
require_once dirname(__DIR__,3).'/config/auth.php';
$loginErro='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email']??'');
    $senha=$_POST['password']??'';
    if(gamehub_security_is_rate_limited($pdo,$email)){
        gamehub_security_record_event($pdo,'login_rate_limited',$email);
        $loginErro='Muitas tentativas. Aguarde alguns minutos e tente novamente.';
        return;
    }
    if(!gamehub_turnstile_verify()){
        gamehub_security_record_event($pdo,'login_failed',$email);
        $loginErro='Verificação anti-bot não concluída.';
        return;
    }
    $s=$pdo->prepare('SELECT id,name,email,password,roles FROM users WHERE LOWER(email)=LOWER(:e) LIMIT 1');
    $s->execute([':e'=>$email]);
    $u=$s->fetch();
    if($u && gamehub_verify_password($pdo,$u,$senha)){
        gamehub_record_login($pdo,(int)$u['id']);
        gamehub_issue_cookie($u);
        header('Location: /home.php'); exit;
    }
    gamehub_security_record_event($pdo,'login_failed',$email);
    $loginErro='Email ou senha incorretos.';
}elseif(gamehub_current_user($pdo)){
    header('Location: /home.php'); exit;
}
