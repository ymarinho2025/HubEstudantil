<?php
$pdo = require __DIR__ . '/../db.php';

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $nome  = trim($_POST['name'] ?? '');
    $senha = $_POST['password'] ?? '';

    if ($email === '' || $nome === '' || $senha === '') {
        http_response_code(400);
        $mensagem = "Todos os campos são obrigatórios.";
    } elseif (mb_strlen($nome) < 2 || mb_strlen($nome) > 50) {
        http_response_code(400);
        $mensagem = "O nome deve ter entre 2 e 50 caracteres.";
    } elseif (mb_strlen($email) > 100) {
        http_response_code(400);
        $mensagem = "Email muito longo.";
    } elseif (strlen($senha) < 8) {
        http_response_code(400);
        $mensagem = "A senha deve conter no mínimo 8 caracteres.";
    } elseif (!preg_match('/^[\p{L}\s.\'-]+$/u', $nome)) {
        http_response_code(400);
        $mensagem = "Nome inválido.";
    } elseif (preg_match('/[<>]/', $nome) || preg_match('/[<>]/', $email)) {
        http_response_code(400);
        $mensagem = "Entrada inválida.";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        http_response_code(400);
        $mensagem = "Email inválido.";
    } else {
        $hash = hash('sha256', $senha);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([
            ':email' => $email
        ]);

        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userExists) {
            http_response_code(409);
            $mensagem = "Email já cadastrado.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)
            ");

            $ok = $stmt->execute([
                ':name'     => $nome,
                ':email'    => $email,
                ':password' => $hash
            ]);

            if ($ok) {
                http_response_code(200);
                $mensagem = "Usuário registrado com sucesso!";
            } else {
                http_response_code(500);
                $mensagem = "Erro ao registrar.";
            }
        }
    }
}
?>
