<?php
// setup.php
require_once 'config.php';

echo "<h2>Iniciando Setup do CRM</h2>";

// 1. Criar o banco se não existir
$dbname = 'crm_db';
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco de dados criado ou já existente.<br>";
    $pdo->exec("USE `$dbname`");
} catch(PDOException $e) {
    die("Erro ao criar banco: " . $e->getMessage());
}

// 2. Rodar o database.sql
$sqlFile = 'database.sql';
if (file_exists($sqlFile)) {
    $sqlContent = file_get_contents($sqlFile);
    try {
        $pdo->exec($sqlContent);
        echo "Tabelas criadas com sucesso.<br>";
    } catch(PDOException $e) {
        die("Erro ao criar tabelas: " . $e->getMessage());
    }
} else {
    die("Arquivo database.sql não encontrado.");
}

// 3. Criar usuário admin padrão
$email = 'teste@teste.com';
$password = '123456';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        $stmt_insert = $pdo->prepare("INSERT INTO users (email, password_hash, is_admin) VALUES (?, ?, 1)");
        $stmt_insert->execute([$email, $passwordHash]);
        echo "Usuário administrador '$email' criado com sucesso.<br>";
    } else {
        echo "Usuário administrador já existe.<br>";
    }
} catch(PDOException $e) {
    die("Erro ao criar usuário: " . $e->getMessage());
}

echo "<h3>Setup conluído!</h3>";
echo "<a href='login.php'>Acessar o sistema</a>";
?>
