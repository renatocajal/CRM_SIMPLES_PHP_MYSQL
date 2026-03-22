<?php
require_once 'config.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $preco = $_POST['preco_padrao'];
        
        if ($nome) {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco_padrao) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $descricao, $preco]);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['produto_id'];
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    header("Location: produtos.php");
    exit;
}

$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC")->fetchAll();
?>

<div class="row border-bottom pb-2 mb-4">
    <div class="col-md-6">
        <h2>Meus Produtos / Serviços</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Novo Produto</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nome do Produto/Serviço *</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor Padrão (R$) *</label>
                        <input type="number" step="0.01" name="preco_padrao" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição (Opcional)</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
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
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Valor Base (R$)</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produtos) === 0): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum produto listado.</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($p['nome']) ?></td>
                            <td class="text-muted"><small><?= htmlspecialchars($p['descricao']) ?></small></td>
                            <td class="text-success fw-bold">R$ <?= number_format($p['preco_padrao'], 2, ',', '.') ?></td>
                            <td class="text-end">
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza? Isso pode afetar quem já o tem listado.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
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
