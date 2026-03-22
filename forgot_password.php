<?php
require_once 'config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        $stmt_reset = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt_reset->execute([$email, $token, $expires]);
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $link = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
        
        // Simulação de e-mail / Exibição local
        $msg = "<div class='alert alert-success mt-3'>
                Instruções enviadas! <br><br>
                <strong>[SIMULAÇÃO DE EMAIL - APENAS DEV]:</strong><br>
                <a href='$link'>$link</a>
                </div>";
    } else {
        $msg = "<div class='alert alert-info mt-3'>Se o e-mail estiver cadastrado, você receberá um link de recuperação.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Esqueci minha senha - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
    </style>
</head>
<body>
    <div class="login-box text-center">
        <h4 class="mb-3">Recuperação de Senha</h4>
        <p class="text-muted">Informe seu e-mail para receber as instruções.</p>
        <form method="POST" action="">
            <div class="mb-3 text-start">
                <input type="email" name="email" class="form-control" placeholder="Seu E-mail" required autofocus>
            </div>
            <button type="submit" class="btn btn-warning w-100">Recuperar Senha</button>
        </form>
        <?= $msg ?>
        <div class="mt-3">
            <a href="login.php" class="text-decoration-none">Voltar ao Login</a>
        </div>
    </div>
</body>
</html>
