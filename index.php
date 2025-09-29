<?php
require_once 'config.php'; // Inclui o arquivo de configuração que inicia sessão e conecta ao banco de dados

// Register
if (isset($_POST['register'])) { // Verifica se o formulário de registro foi submetido (via POST com nome 'register')
    $user = $_POST['username']; // Captura o username do formulário POST
    $pass = $_POST['password']; // Captura a senha do formulário POST 
    if (!empty($user) && !empty($pass)) { // Verifica se username e senha não estão vazios
        $hashed = hashPassword($pass); // Gera o hash da senha usando a função definida em config.php
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)"); // Gera o hash da senha usando a função definida em config.php
        if ($stmt->execute([$user, $hashed])) {  // Executa a query com os valores sanitizados (username e hash da senha)
            $message = "Usuário registrado! Faça login."; // Define uma mensagem de sucesso se o registro foi bem-sucedido
        } else {
            $error = "Erro ao registrar. Usuário pode já existir."; // Define uma mensagem de erro se a inserção falhou (ex: username duplicado)
        }
    }
}

// Login
if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $row = $stmt->fetch();
    if ($row && verifyPassword($pass, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Credenciais inválidas!";
    }
}
?>

<!DOCTYPE html> <!-- Inicio do corpo do projeto --> 
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueTask Manager - Login</title> <!-- Titulo do projeto -->
    <link rel="stylesheet" href="assets/style.css"> <!--Implementa o CSS -->
</head>
<body>
    <div class="container">
        <h1>BlueTask Manager</h1> <!--Titulo da página -->
        <div class="form-container">
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>

            <!-- Forma Login -->
            <form method="POST">
                <h2>Login</h2> <!-- Segundo titulo -->
                <input type="text" name="username" placeholder="Usuário" required>
                <input type="password" name="password" placeholder="Senha" required>
                <button type="submit" name="login" class="btn-primary">Entrar</button>
            </form>

            <!-- Forma Registro -->
            <form method="POST"> 
                <h2>Registrar</h2> <!-- Texto secundário -->
                <input type="text" name="username" placeholder="Novo Usuário" required> <!--Implementação do botão Novo Usuário -->
                <input type="password" name="password" placeholder="Nova Senha" required> <!-- Implementação do botão Nova Senha --> 
                <button type="submit" name="register" class="btn-secondary">Registrar</button> <!-- Implementação do botão Registrar -->
            </form>
        </div>
    </div>
</body>
</html> <!-- Fecha a tag html -->