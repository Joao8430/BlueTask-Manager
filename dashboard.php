<?php
// Inclui o arquivo de configuração que inicia sessão e conecta ao banco de dados
require_once 'config.php';

// Verifica se o usuário está logado (user_id na sessão); caso contrário, redireciona para login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Captura o ID do usuário logado da sessão
$user_id = $_SESSION['user_id'];

// Verifica se o formulário de adicionar tarefa foi submetido (POST com nome 'add_task')
if (isset($_POST['add_task'])) {
    // Captura o título da tarefa do formulário POST
    $title = $_POST['title'];
    // Captura a descrição da tarefa do formulário POST
    $desc = $_POST['description'];
    // Prepara uma query SQL para inserir a nova tarefa (prepared statement)
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)");
    // Executa a query com user_id, título e descrição sanitizados
    $stmt->execute([$user_id, $title, $desc]);
    // Redireciona para a mesma página após inserção (evita reenvio de form)
    header("Location: dashboard.php");
    exit;
}

// Verifica se o formulário de editar tarefa foi submetido (POST com nome 'edit_task')
if (isset($_POST['edit_task'])) {
    // Captura o ID da tarefa do formulário POST
    $id = $_POST['id'];
    // Captura o novo título do formulário POST
    $title = $_POST['title'];
    // Captura a nova descrição do formulário POST
    $desc = $_POST['description'];
    // Captura o status da tarefa do formulário POST
    $status = $_POST['status'];
    // Prepara uma query SQL para atualizar a tarefa (apenas para o usuário dono)
    $stmt = $pdo->prepare("UPDATE tasks SET title=?, description=?, status=? WHERE id=? AND user_id=?");
    // Executa a query com valores sanitizados
    $stmt->execute([$title, $desc, $status, $id, $user_id]);
    // Redireciona para a mesma página após atualização
    header("Location: dashboard.php");
    exit;
}

// Verifica se uma tarefa deve ser deletada (via GET com parâmetro 'delete')
if (isset($_GET['delete'])) {
    // Captura o ID da tarefa do parâmetro GET
    $id = $_GET['delete'];
    // Prepara uma query SQL para deletar a tarefa (apenas para o usuário dono)
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id=? AND user_id=?");
    // Executa a query com ID e user_id sanitizados
    $stmt->execute([$id, $user_id]);
    // Redireciona para a mesma página após deleção
    header("Location: dashboard.php");
    exit;
}

// Verifica se o logout foi solicitado (via GET com parâmetro 'logout')
if (isset($_GET['logout'])) {
    // Destrói todas as variáveis de sessão (logout)
    session_destroy();
    // Redireciona para a página de login
    header("Location: index.php");
    exit;
}

// Prepara uma query SQL para buscar todas as tarefas do usuário logado, ordenadas por data de criação (mais recentes primeiro)
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
// Busca todos os resultados como um array de arrays associativos
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<!-- Declara o tipo de documento como HTML5 -->
<html lang="pt-BR">
<!-- Define o idioma da página como português brasileiro -->
<head>
    <!-- Define a codificação de caracteres como UTF-8 -->
    <meta charset="UTF-8">
    <!-- Configura a viewport para responsividade -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Define o título da página -->
    <title>Dashboard - BlueTask Manager</title>
    <!-- Inclui o arquivo CSS externo -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Container principal -->
    <div class="container">
        <!-- Header do dashboard com título e saudação -->
        <header>
            <!-- Título principal -->
            <h1>BlueTask Manager</h1>
            <!-- Saudação personalizada com username da sessão -->
            <p>Bem-vindo, <?php echo $_SESSION['username']; ?>!</p>
            <!-- Link para logout com confirmação JS (classe para estilo) -->
            <a href="?logout=1" class="btn-logout" onclick="return confirm('Tem certeza?')">Logout</a>
        </header>

        <!-- Formulário para adicionar nova tarefa (método POST) -->
        <form method="POST" class="task-form">
            <!-- Campo de input para título da tarefa (obrigatório) -->
            <input type="text" name="title" placeholder="Título da Tarefa" required>
            <!-- Campo textarea para descrição (opcional) -->
            <textarea name="description" placeholder="Descrição"></textarea>
            <!-- Botão de submit para adicionar tarefa -->
            <button type="submit" name="add_task" class="btn-secondary">Adicionar Tarefa</button>
        </form>

        <!-- Seção para listar tarefas -->
        <div class="tasks-list">
            <!-- Título da seção de tarefas -->
            <h2>Suas Tarefas</h2>
            <!-- Verifica se não há tarefas; exibe mensagem se vazio -->
            <?php if (empty($tasks)): ?>
                <p>Nenhuma tarefa encontrada. Adicione uma!</p>
            <?php else: ?>
                <!-- Loop foreach para exibir cada tarefa -->
                <?php foreach ($tasks as $task): ?>
                    <!-- Item da tarefa com classe condicional baseada no status -->
                    <div class="task-item <?php echo $task['status'] == 'completed' ? 'completed' : ''; ?>">
                        <!-- Título da tarefa (escapado para segurança XSS) -->
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <!-- Descrição da tarefa (escapada) -->
                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                        <!-- Data de criação formatada -->
                        <small>Criada em: <?php echo $task['created_at']; ?></small>
                        
                        <!-- Formulário inline para editar a tarefa (método POST) -->
                        <form method="POST" style="display: inline;">
                            <!-- Campo hidden para ID da tarefa -->
                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                            <!-- Campo de input para editar título (valor atual preenchido) -->
                            <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                            <!-- Textarea para editar descrição (valor atual preenchido) -->
                            <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea>
                            <!-- Select para status da tarefa (opções com selected condicional) -->
                            <select name="status">
                                <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Concluída</option>
                            </select>
                            <!-- Botão de submit para salvar edição -->
                            <button type="submit" name="edit_task" class="btn-secondary">Salvar</button>
                        </form>
                        
                        <!-- Link para deletar tarefa com confirmação JS -->
                        <a href="?delete=<?php echo $task['id']; ?>" class="btn-danger" onclick="return confirm('Deletar esta tarefa?')">Deletar</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- Inclui o arquivo JavaScript externo para interatividade -->
    <script src="assets/script.js"></script>
</body>
<!-- Fecha a tag HTML -->
</html>