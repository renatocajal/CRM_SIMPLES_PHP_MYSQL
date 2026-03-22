<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
$msg = '';
$valid = false;
$email = '';

if ($token) {
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        $valid = true;
        $email = $reset['email'];
    } else {
        $msg = "<div class='alert alert-danger'>Token inválido ou expirado. Faça o pedido novamente.</div>";
    }
} else {
     $msg = "<div class='alert alert-danger'>Token não informado.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if ($password === $confirm) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt_upd->execute([$hash, $email]);
        
        $stmt_del = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt_del->execute([$token]);
        
        header("Location: login.php?reset=1");
        exit;
    } else {
        $msg = "<div class='alert alert-danger'>As senhas não coincidem.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h4 class="text-center mb-4">Nova Senha</h4>
        <?= $msg ?>
        <?php if ($valid): ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" name="password" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirme a Senha</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Salvar Senha</button>
        </form>
        <?php else: ?>
            <div class="text-center mt-3">
                <a href="forgot_password.php" class="btn btn-warning w-100">Tentar novamente</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
