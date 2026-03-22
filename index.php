<?php
require_once 'config.php';
require_once 'includes/header.php';

$today = date('Y-m-d');

// Metas
$meta_diaria = 20;

// Prospecção de Hoje (Primeiro contato)
$stmt = $pdo->prepare("SELECT COUNT(*) as qtd FROM interacoes WHERE tipo = 'primeiro_contato' AND DATE(data) = ?");
$stmt->execute([$today]);
$prospeccao_hoje = $stmt->fetch()['qtd'];

// Follow-ups de Hoje
$stmt = $pdo->prepare("SELECT COUNT(*) as qtd FROM interacoes WHERE tipo = 'follow_up' AND DATE(data) = ?");
$stmt->execute([$today]);
$followups_hoje = $stmt->fetch()['qtd'];

$faltam = max(0, $meta_diaria - $prospeccao_hoje);
$progresso = min(100, ($prospeccao_hoje / $meta_diaria) * 100);

// Tarefas Atrasadas ou do Dia (Pendentes)
$stmt_tarefas = $pdo->prepare("
    SELECT t.*, c.nome as contato_nome 
    FROM tarefas t
    JOIN contatos c ON t.contato_id = c.id 
    WHERE t.status = 'Pendente' AND DATE(t.data) <= ?
    ORDER BY t.data ASC
");
$stmt_tarefas->execute([$today]);
$tarefas_pendentes = $stmt_tarefas->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3">Meu Dashboard</h2>
    </div>
</div>

<!-- Progresso de Metas -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Meta de Prospecção Diária</h5>
                <h2 class="mb-3 text-primary"><?= $prospeccao_hoje ?> <small class="text-muted fs-5">/ <?= $meta_diaria ?></small></h2>
                
                <div class="progress mb-3" style="height: 25px;">
                  <div class="progress-bar <?= $progresso >= 100 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $progresso ?>%;">
                      <?= number_format($progresso, 0) ?>%
                  </div>
                </div>
                
                <?php if ($prospeccao_hoje >= $meta_diaria): ?>
                    <div class="alert alert-success fw-bold p-2 mb-0">
                        <i class="fas fa-trophy"></i> Parabéns, vc bateu sua meta de prospecção. Agora negocie.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning fw-bold p-2 mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Faltam <?= $faltam ?> contatos para atingir a meta.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 bg-info text-white">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5>Follow-ups Efetuados Hoje</h5>
                <h1 class="display-3 fw-bold"><?= $followups_hoje ?></h1>
                <p class="mb-0">Interações de retorno</p>
            </div>
        </div>
    </div>
</div>

<!-- Tarefas Pendentes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-tasks"></i> Tarefas Pendentes (Hoje ou Atrasadas)</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data/Hora</th>
                            <th>Contato</th>
                            <th>Descrição</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tarefas_pendentes) > 0): ?>
                            <?php foreach ($tarefas_pendentes as $t): ?>
                            <tr>
                                <td><span class="text-danger fw-bold"><?= date('d/m/Y H:i', strtotime($t['data'])) ?></span></td>
                                <td><a href="contato_detalhe.php?id=<?= $t['contato_id'] ?>" class="text-decoration-none fw-bold"><?= htmlspecialchars($t['contato_nome']) ?></a></td>
                                <td><?= htmlspecialchars($t['descricao']) ?></td>
                                <td class="text-end">
                                    <form method="POST" action="contato_detalhe.php?id=<?= $t['contato_id'] ?>" style="display:inline;">
                                        <input type="hidden" name="action" value="concluir_tarefa">
                                        <input type="hidden" name="tarefa_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="Concluir"><i class="fas fa-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma tarefa pendente urgante. Você está limpo!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
