<?php
require_once __DIR__ . '/../src/Controllers/login/storeLogin.php';
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Grade | Login</title>
    <link rel="stylesheet" href="/css/form.css">
<?php if (hub_turnstile_enabled()): ?><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script><?php endif; ?>
</head>
<body>

<div class="login-root">
    <aside class="side-left">
        <div class="egrade-logo">
            <span class="egrade-logo__icon">E</span>
            <span class="egrade-logo__text">Grade</span>
        </div>
    </aside>

    <section class="side-right">
        <div class="login-card">
            <div class="login-header">
                <div class="grid-overlay"></div>

                <div class="brand">
                    <span class="brand-icon">E</span>
                    <span class="brand-text">Grade</span>
                </div>

                <p class="brand-sub">Acesse sua conta institucional</p>
            </div>

            <?php if (!empty($loginErro)): ?>
                <p class="form-error"><?= htmlspecialchars($loginErro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <fieldset>
                    <legend>Entre com seus dados de acesso</legend>
                    <label for="email">Email:</label>
                    <input type="text" name="email" placeholder="Email" required autofocus>
                    <label for="password">Senha:</label>
                    <input type="password" name="password" placeholder="Senha" required>
                    <?php if (hub_turnstile_enabled()): ?><div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(hub_turnstile_site_key(), ENT_QUOTES, 'UTF-8') ?>"></div><?php endif; ?>
                    <input type="submit" value="Entrar">
                </fieldset>
                <p class="form-link">Não tem uma conta? <a href="register.php">Registre-se</a></p>
            </form>
        </div>
    </section>
</div>

<?= hub_security_telemetry_script('form') ?>
</body>
</html>