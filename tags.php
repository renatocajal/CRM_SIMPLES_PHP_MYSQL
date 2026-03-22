<?php
require_once 'config.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nome = trim($_POST['nome']);
        $cor = $_POST['cor'] ?: '#6c757d';
        
        if ($nome) {
            $stmt = $pdo->prepare("INSERT INTO tags (nome, cor) VALUES (?, ?)");
            $stmt->execute([$nome, $cor]);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['tag_id'];
        $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    header("Location: tags.php");
    exit;
}

$tags = $pdo->query("SELECT * FROM tags ORDER BY nome ASC")->fetchAll();
?>

<div class="row border-bottom pb-2 mb-4">
    <div class="col-md-6">
        <h2>Classificadores & Tags</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Criar Nova Tag</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nome da Tag (Ex: Quente) *</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <label class="form-label me-3 mb-0">Cor Visual</label>
                        <input type="color" name="cor" class="form-control form-control-color w-25" value="#0d6efd" title="Escolha a cor da tag">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Criar Etiqueta</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Visão / Layout</th>
                            <th>Nome Identificador</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tags) === 0): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">Ainda não existem categorias.</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach ($tags as $t): ?>
                        <tr>
                            <td>
                                <span class="tag-badge" style="background-color: <?= $t['cor'] ?>;">
                                    <?= htmlspecialchars($t['nome']) ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($t['nome']) ?></td>
                            <td class="text-end">
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Ao apagar esta tag, ela sumirá de todos os contatos que a possuem. Continuar?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="tag_id" value="<?= $t['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
