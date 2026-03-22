<?php
require_once 'config.php';
require_once 'includes/auth.php';

// Apenas aceita requisições se logado
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contato_id = $_POST['contato_id'] ?? 0;
    $status = $_POST['status'] ?? '';

    $valid_statuses = ['Lead novo', 'Primeiro contato', 'Em negociação', 'Fechado', 'Perdido'];

    if ($contato_id && in_array($status, $valid_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE contatos SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $contato_id])) {
                echo json_encode(['success' => true]);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
