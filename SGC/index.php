<?php
session_start();
require_once __DIR__ . '/config/auth.php';

$pdo = hub_pdo();
$loginErro = '';

if (isset($_GET['logout'])) {
    hub_clear_auth_cookie();
    header('Location: /index.php');
    exit;
}

if (hub_current_user($pdo)) {
    header('Location: /home/');
    exit;
}

if (empty($_SESSION['sgc_captcha'])) {
    $_SESSION['sgc_captcha'] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
}

if (isset($_GET['captcha'])) {
    $_SESSION['sgc_captcha'] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
    header('Location: /index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $code = strtoupper(trim($_POST['code'] ?? ''));

    if ($login === '' || $password === '' || $code === '') {
        $loginErro = 'Preencha todos os campos.';
    } elseif (!hash_equals((string)$_SESSION['sgc_captcha'], $code)) {
        $loginErro = 'Código de verificação incorreto.';
        $_SESSION['sgc_captcha'] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password, roles
             FROM users
             WHERE LOWER(email) = LOWER(:login) OR LOWER(name) = LOWER(:login)
             LIMIT 1'
        );
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && hub_verify_password_and_upgrade($pdo, $user, $password)) {
            hub_record_login($pdo, (int)$user['id']);
            hub_issue_auth_cookie($user);
            unset($_SESSION['sgc_captcha']);
            header('Location: /home/');
            exit;
        }

        $loginErro = 'Usuário/e-mail ou senha incorretos.';
        $_SESSION['sgc_captcha'] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Club Management System</title>
    <link rel="stylesheet" href="./src/css/style.css">
    <link rel="stylesheet" href="./src/css/form.css">
    <style>
      .auth-error{color:#7d0000;background:#fff8;padding:8px;border-radius:4px;margin:8px 0;font-size:12px}
      #captcha a{color:inherit;text-decoration:none;display:block}
    </style>
</head>
<body>
    <header><img src="./src/images/dsa.png" alt="Logos dos clubes"></header>
    <h1 id="title">Club Management System</h1>
    <main>
        <form id="loginForm" method="post" action="/index.php">
            <label id="userLabel" for="login">Your username:</label>
            <input id="login" name="login" autocomplete="username" placeholder="Email or username" required>
            <label id="passLabel" for="senha">Your password:</label>
            <input id="senha" name="password" type="password" minlength="8" maxlength="255"
                autocomplete="current-password" placeholder="Type the password" required>

            <div id="captcha" title="Clique para gerar outro código">
              <a href="/index.php?captcha=1"><?= htmlspecialchars($_SESSION['sgc_captcha']) ?></a>
            </div>

            <label id="codeLabel" for="codigo">Complete the code:</label>
            <input id="codigo" name="code" maxlength="4" placeholder="CODE DISPLAYED ABOVE" autocomplete="off" required>

            <?php if ($loginErro !== ''): ?>
              <div class="auth-error"><?= htmlspecialchars($loginErro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <button id="submitText" type="submit">To access CMS</button>
            <a id="accountText" class="button" href="/register.php">Request An Account</a>

            <div class="flags">
                <img src="./src/images/brasil.jfif" alt="Português" data-lang="pt">
                <img src="./src/images/espanha.jfif" alt="Español" data-lang="es">
                <img src="./src/images/eua.jfif" alt="English" data-lang="en">
            </div>

            <div class="links">
              <a id="privacyText"
                href="https://www.adventistas.org/pt/institucional/organizacao/politica-de-privacidade-de-dados-da-igreja-adventista-do-setimo-dia/"
                target="_blank">Privacy Policy</a>
              <a id="forgotText" href="/register.php">Forgot password?</a>
            </div>
            <div id="msg" aria-live="polite"></div>
        </form>
    </main>

<script>
const translations = {
 en:{title:'Club Management System',user:'Your username or email:',userP:'Email or username',pass:'Your password:',passP:'Type the password',code:'Complete the code:',codeP:'CODE DISPLAYED ABOVE',submit:'To access CMS',account:'Request An Account',privacy:'Privacy Policy',forgot:'Create / recover account'},
 pt:{title:'Sistema de Gestão de Clubes',user:'Seu usuário ou e-mail:',userP:'Digite seu e-mail ou usuário',pass:'Sua senha:',passP:'Digite sua senha',code:'Complete o código:',codeP:'CÓDIGO EXIBIDO ACIMA',submit:'Acessar o CMS',account:'Solicitar uma conta',privacy:'Política de Privacidade',forgot:'Criar / recuperar conta'},
 es:{title:'Sistema de Gestión de Clubes',user:'Su usuario o correo:',userP:'Ingrese correo o usuario',pass:'Su contraseña:',passP:'Ingrese su contraseña',code:'Complete el código:',codeP:'CÓDIGO MOSTRADO ARRIBA',submit:'Acceder al CMS',account:'Solicitar una cuenta',privacy:'Política de Privacidad',forgot:'Crear / recuperar cuenta'}
};
function setLang(l){
 const x=translations[l];
 title.textContent=x.title; userLabel.textContent=x.user; login.placeholder=x.userP;
 passLabel.textContent=x.pass; senha.placeholder=x.passP; codeLabel.textContent=x.code;
 codigo.placeholder=x.codeP; submitText.textContent=x.submit; accountText.textContent=x.account;
 privacyText.textContent=x.privacy; forgotText.textContent=x.forgot;
}
document.querySelectorAll('[data-lang]').forEach(i=>i.onclick=()=>setLang(i.dataset.lang));
setLang('en');
</script>
</body>
</html>
