<?php
// Inicia a sessão PHP para gerenciar autenticação de usuários (ex: armazenar user_id após login)
session_start(); 

// Define a variável para o host do banco de dados MySQL (padrão local: localhost)
$host = 'localhost';
// Define o nome do banco de dados a ser usado
$dbname = 'bluetask_db';
// Define o usuário do banco de dados (padrão XAMPP: root)
$username = 'root';
// Define a senha do banco de dados (padrão XAMPP: vazia)
$password = '';

// Inicia um bloco try-catch para lidar com exceções na conexão PDO
try {
    // Cria uma nova instância PDO para conectar ao MySQL, usando host e nome do DB
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configura o PDO para lançar exceções em caso de erro (modo de erro estrito)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Em caso de erro na conexão, exibe a mensagem de erro e para a execução
    die("Erro na conexão: " . $e->getMessage());
}

// Define uma função personalizada para hashear senhas usando password_hash (segurança)
function hashPassword($pass) {
    // Retorna o hash da senha usando o algoritmo padrão do PHP (BCRYPT)
    return password_hash($pass, PASSWORD_DEFAULT);
}

// Define uma função personalizada para verificar se uma senha corresponde ao hash armazenado
function verifyPassword($pass, $hash) {
    // Usa password_verify para comparar a senha em texto plano com o hash
    return password_verify($pass, $hash);
}
?>