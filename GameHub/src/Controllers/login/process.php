<?php
$pdo=require __DIR__.'/../../db.php';
require_once dirname(__DIR__,3).'/config/auth.php';
$mensagem='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome=trim($_POST['name']??''); $email=trim($_POST['email']??''); $senha=$_POST['password']??'';
    if(gamehub_security_is_rate_limited($pdo,$email)){ gamehub_security_record_event($pdo,'register_rate_limited',$email); $mensagem='Muitas tentativas. Aguarde alguns minutos e tente novamente.'; }
    elseif(!gamehub_turnstile_verify()){ gamehub_security_record_event($pdo,'register_failed',$email); $mensagem='Verificação anti-bot não concluída.'; }
    elseif($nome===''||$email===''||$senha==='') $mensagem='Todos os campos são obrigatórios.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $mensagem='Email inválido.';
    elseif(mb_strlen($nome)<2||mb_strlen($nome)>50) $mensagem='Nome deve ter entre 2 e 50 caracteres.';
    elseif(strlen($senha)<8) $mensagem='A senha deve ter no mínimo 8 caracteres.';
    else{
        $s=$pdo->prepare('SELECT id FROM users WHERE LOWER(email)=LOWER(:e) LIMIT 1'); $s->execute([':e'=>$email]);
        if($s->fetch()) $mensagem='Email já cadastrado.';
        else{
            $s=$pdo->prepare('INSERT INTO users(name,email,password,roles) VALUES(:n,:e,:p,1) RETURNING id,name,email,roles');
            $s->execute([':n'=>$nome,':e'=>$email,':p'=>password_hash($senha,PASSWORD_DEFAULT)]);
            $u=$s->fetch(); gamehub_security_record_event($pdo,'register_success',$email,(int)$u['id']); gamehub_issue_cookie($u); gamehub_record_login($pdo,(int)$u['id']);
            header('Location: /home.php'); exit;
        }
    }
}
