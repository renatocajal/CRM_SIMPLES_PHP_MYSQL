<?php
require_once 'config.php';
require_once 'includes/header.php';

$stmt = $pdo->query("SELECT id, nome, nicho, status, ultima_interacao FROM contatos ORDER BY created_at DESC");
$contatos = $stmt->fetchAll();

$board = [
    'Lead novo' => [],
    'Primeiro contato' => [],
    'Em negociação' => [],
    'Fechado' => [],
    'Perdido' => []
];

foreach ($contatos as $c) {
    if (isset($board[$c['status']])) {
        $board[$c['status']][] = $c;
    }
}
?>

<style>
    .kanban-board {
        display: flex;
        overflow-x: auto;
        padding-bottom: 20px;
        min-height: 70vh;
    }
    .kanban-col {
        min-width: 300px;
        max-width: 300px;
        background-color: #ebecf0;
        border-radius: 8px;
        padding: 10px;
        margin-right: 15px;
        display: flex;
        flex-direction: column;
    }
    .kanban-col-header {
        font-weight: 600;
        margin-bottom: 10px;
        padding: 5px;
        border-bottom: 2px solid #ccc;
        display: flex;
        justify-content: space-between;
    }
    .kanban-cards {
        flex-grow: 1;
        min-height: 50px;
    }
    .kanban-card {
        background: #fff;
        padding: 12px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 10px;
        cursor: grab;
        border-left: 4px solid transparent;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    
    /* Cores das colunas para os cards */
    .col-Lead-novo .kanban-card { border-left-color: #6c757d; }
    .col-Primeiro-contato .kanban-card { border-left-color: #0d6efd; }
    .col-Em-negociacao .kanban-card { border-left-color: #ffc107; }
    .col-Fechado .kanban-card { border-left-color: #198754; }
    .col-Perdido .kanban-card { border-left-color: #dc3545; }

    .kanban-card-title {
        font-weight: bold;
        font-size: 0.95em;
        margin-bottom: 5px;
        color: #333;
        text-decoration: none;
        display: block;
    }
    .kanban-card-title:hover {
        color: #0d6efd;
    }
    .kanban-card-meta {
        font-size: 0.8em;
        color: #777;
    }
</style>

<div class="row mb-3">
    <div class="col-12">
        <h2>Pipeline de Vendas</h2>
    </div>
</div>

<div class="kanban-board">
    <?php foreach ($board as $status => $lista): ?>
        <?php $safe_class = "col-" . str_replace([' ', 'ç', 'ã'], ['-', 'c', 'a'], $status); ?>
        <div class="kanban-col <?= $safe_class ?>">
            <div class="kanban-col-header">
                <span><?= htmlspecialchars($status) ?></span>
                <span class="badge bg-secondary rounded-pill"><?= count($lista) ?></span>
            </div>
            <div class="kanban-cards" data-status="<?= htmlspecialchars($status) ?>">
                <?php foreach ($lista as $c): ?>
                    <div class="kanban-card" data-id="<?= $c['id'] ?>">
                        <a href="contato_detalhe.php?id=<?= $c['id'] ?>" class="kanban-card-title"><?= htmlspecialchars($c['nome']) ?></a>
                        <div class="kanban-card-meta">
                            <i class="fas fa-briefcase"></i> <?= htmlspecialchars($c['nicho'] ?: 'S/ Nicho') ?><br>
                            <?php if ($c['ultima_interacao']): ?>
                                <?php
                                    $dias = date_diff(date_create($c['ultima_interacao']), date_create('now'))->days;
                                ?>
                                <i class="far fa-clock"></i> <?= $dias ?> d atrás
                            <?php else: ?>
                                <i class="far fa-clock"></i> Novo
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const columns = document.querySelectorAll('.kanban-cards');
        columns.forEach(function(col) {
            new Sortable(col, {
                group: 'shared', // conjunto de listas em que se pode mover interligadas
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: function (evt) {
                    const itemEl = evt.item;
                    const contatoId = itemEl.getAttribute('data-id');
                    const newStatus = evt.to.getAttribute('data-status');
                    const oldStatus = evt.from.getAttribute('data-status');
                    
                    if (newStatus !== oldStatus) {
                        // Faz a request AJAX para atualizar o status no banco
                        fetch('update_pipeline.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `contato_id=${contatoId}&status=${encodeURIComponent(newStatus)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(!data.success) {
                                alert('Erro ao atualizar status do card.');
                                // reverter a alteração visualmente (opcionalmente fazer refresh)
                                window.location.reload();
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Erro de conexão ao mover card.');
                        });
                    }
                }
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
