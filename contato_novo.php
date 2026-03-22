<?php
require_once 'config.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $telefone = preg_replace('/\D/', '', $_POST['telefone']); // salva só digito
    $email = $_POST['email'];
    $nicho = $_POST['nicho'];
    $cidade = $_POST['cidade'];
    $origem = $_POST['origem'];
    $observacoes = $_POST['observacoes'];
    $status = $_POST['status'] ?: 'Lead novo';
    
    $stmt = $pdo->prepare("INSERT INTO contatos (nome, telefone, email, nicho, cidade, status, origem, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $telefone, $email, $nicho, $cidade, $status, $origem, $observacoes]);
    $id = $pdo->lastInsertId();
    
    header("Location: contato_detalhe.php?id=" . $id);
    exit;
}
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Novo Contato</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="contatos.php" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefone / WhatsApp</label>
                    <input type="text" name="telefone" class="form-control" placeholder="Apenas números, ex: 11999999999">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nicho</label>
                    <input type="text" name="nicho" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="cidade" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Origem do Contato</label>
                    <input type="text" name="origem" class="form-control" placeholder="Indicação, Instagram, etc">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Inicial</label>
                    <select name="status" class="form-select">
                        <option value="Lead novo">Lead novo</option>
                        <option value="Primeiro contato">Primeiro contato</option>
                        <option value="Em negociação">Em negociação</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Contato</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
