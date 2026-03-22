<?php
require_once 'config.php';
require_once 'includes/header.php';

$filter_status = $_GET['status'] ?? '';
$filter_tag = $_GET['tag'] ?? '';

$where = ["1=1"];
$params = [];

if ($filter_status) {
    $where[] = "c.status = ?";
    $params[] = $filter_status;
}

if ($filter_tag) {
    // Para simplificar, faremos um IN
    $where[] = "c.id IN (SELECT contato_id FROM contato_tags WHERE tag_id = ?)";
    $params[] = $filter_tag;
}

$whereClause = implode(" AND ", $where);

$sql = "SELECT c.*, 
        DATEDIFF(NOW(), c.ultima_interacao) as dias_sem_interacao 
        FROM contatos c 
        WHERE $whereClause 
        ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contatos = $stmt->fetchAll();

$tags = $pdo->query("SELECT * FROM tags ORDER BY nome ASC")->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Contatos</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="contato_novo.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Contato</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body bg-light">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Todos os Status --</option>
                    <option value="Lead novo" <?= $filter_status == 'Lead novo' ? 'selected' : '' ?>>Lead novo</option>
                    <option value="Primeiro contato" <?= $filter_status == 'Primeiro contato' ? 'selected' : '' ?>>Primeiro contato</option>
                    <option value="Em negociação" <?= $filter_status == 'Em negociação' ? 'selected' : '' ?>>Em negociação</option>
                    <option value="Fechado" <?= $filter_status == 'Fechado' ? 'selected' : '' ?>>Fechado</option>
                    <option value="Perdido" <?= $filter_status == 'Perdido' ? 'selected' : '' ?>>Perdido</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="tag" class="form-select">
                    <option value="">-- Todas as Tags --</option>
                    <?php foreach ($tags as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $filter_tag == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="contatos.php" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>WhatsApp</th>
                        <th>Status</th>
                        <th>Última Interação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contatos as $c): ?>
                    <?php
                        $esfriando = false;
                        if (!in_array($c['status'], ['Fechado', 'Perdido'])) {
                            if ($c['dias_sem_interacao'] !== null && $c['dias_sem_interacao'] > 3 || $c['ultima_interacao'] === null) {
                                $esfriando = true;
                            }
                        }
                    ?>
                    <tr>
                        <td>
                            <a href="contato_detalhe.php?id=<?= $c['id'] ?>" class="text-decoration-none fw-bold">
                                <?= htmlspecialchars($c['nome']) ?>
                            </a>
                            <?php if ($esfriando): ?>
                                <span class="badge bg-danger ms-2" title="Sem interação há mais de 3 dias"><i class="fas fa-snowflake"></i> Esfriando</span>
                            <?php endif; ?>
                            <br><small class="text-muted"><?= htmlspecialchars($c['nicho']) ?> - <?= htmlspecialchars($c['cidade']) ?></small>
                        </td>
                        <td>
                            <?php if ($c['telefone']): ?>
                                <a href="https://wa.me/55<?= preg_replace('/\D/', '', $c['telefone']) ?>" target="_blank" class="text-success text-decoration-none fw-bold"><i class="fab fa-whatsapp fs-5"></i> <?= htmlspecialchars($c['telefone']) ?></a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $c['status'] ?></span>
                        </td>
                        <td>
                            <?= $c['ultima_interacao'] ? date('d/m/Y H:i', strtotime($c['ultima_interacao'])) : '<span class="text-muted">Nunca</span>' ?>
                        </td>
                        <td class="text-end">
                            <a href="contato_detalhe.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Detalhes</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (count($contatos) === 0): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum contato encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
