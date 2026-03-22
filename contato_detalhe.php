<?php
require_once 'config.php';
require_once 'includes/header.php';

$id = $_GET['id'] ?? 0;

// Fetch Contact
$stmt = $pdo->prepare("SELECT * FROM contatos WHERE id = ?");
$stmt->execute([$id]);
$contato = $stmt->fetch();

if (!$contato) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Contato não encontrado.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

// Handles POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'edit_contato') {
        $nome = $_POST['nome'];
        $telefone = preg_replace('/\D/', '', $_POST['telefone']);
        $email = $_POST['email'];
        $nicho = $_POST['nicho'];
        $cidade = $_POST['cidade'];
        $origem = $_POST['origem'];
        $status = $_POST['status'];
        $obs = $_POST['observacoes'];
        
        $sql = "UPDATE contatos SET nome=?, telefone=?, email=?, nicho=?, cidade=?, origem=?, status=?, observacoes=? WHERE id=?";
        $pdo->prepare($sql)->execute([$nome, $telefone, $email, $nicho, $cidade, $origem, $status, $obs, $id]);
        
    } elseif ($action === 'add_interacao') {
        $tipo = $_POST['tipo'];
        $descricao = $_POST['descricao'];
        
        $pdo->prepare("INSERT INTO interacoes (contato_id, tipo, descricao, data) VALUES (?, ?, ?, NOW())")->execute([$id, $tipo, $descricao]);
        $pdo->prepare("UPDATE contatos SET ultima_interacao = NOW() WHERE id = ?")->execute([$id]);
        
        if ($contato['status'] === 'Lead novo' && $tipo === 'primeiro_contato') {
            $pdo->prepare("UPDATE contatos SET status = 'Primeiro contato' WHERE id = ?")->execute([$id]);
        }
        
    } elseif ($action === 'add_tarefa') {
        $descricao = $_POST['descricao'];
        $data = $_POST['data'];
        if ($data) {
            $pdo->prepare("INSERT INTO tarefas (contato_id, descricao, data) VALUES (?, ?, ?)")->execute([$id, $descricao, $data]);
        }
        
    } elseif ($action === 'concluir_tarefa') {
        $tarefa_id = $_POST['tarefa_id'];
        $pdo->prepare("UPDATE tarefas SET status = 'Concluída' WHERE id = ? AND contato_id = ?")->execute([$tarefa_id, $id]);
        
    } elseif ($action === 'add_produto') {
        $produto_id = $_POST['produto_id'];
        $preco = $_POST['preco_negociado'];
        $qtd = $_POST['quantidade'];
        $status_prod = $_POST['status_produto'];
        
        $pdo->prepare("INSERT INTO contato_produtos (contato_id, produto_id, preco_negociado, quantidade, status) VALUES (?, ?, ?, ?, ?)")->execute([$id, $produto_id, $preco, $qtd, $status_prod]);
        
    } elseif ($action === 'update_produto') {
        $cp_id = $_POST['cp_id'];
        $status_prod = $_POST['status_produto'];
        $pdo->prepare("UPDATE contato_produtos SET status = ? WHERE id = ? AND contato_id = ?")->execute([$status_prod, $cp_id, $id]);
        
    } elseif ($action === 'add_tag') {
        $tag_id = $_POST['tag_id'];
        // Insert ignore
        $pdo->prepare("INSERT IGNORE INTO contato_tags (contato_id, tag_id) VALUES (?, ?)")->execute([$id, $tag_id]);
        
    } elseif ($action === 'remove_tag') {
        $tag_id = $_POST['tag_id'];
        $pdo->prepare("DELETE FROM contato_tags WHERE contato_id = ? AND tag_id = ?")->execute([$id, $tag_id]);
    }
    
    header("Location: contato_detalhe.php?id=$id");
    exit;
}

// Fetch Relations
$interacoes = $pdo->prepare("SELECT * FROM interacoes WHERE contato_id = ? ORDER BY data DESC");
$interacoes->execute([$id]);
$interacoes = $interacoes->fetchAll();

$tarefas = $pdo->prepare("SELECT * FROM tarefas WHERE contato_id = ? ORDER BY status DESC, data ASC");
$tarefas->execute([$id]);
$tarefas = $tarefas->fetchAll();

$produtos_negociados = $pdo->prepare("SELECT cp.*, p.nome FROM contato_produtos cp JOIN produtos p ON cp.produto_id = p.id WHERE cp.contato_id = ? ORDER BY cp.created_at DESC");
$produtos_negociados->execute([$id]);
$produtos_negociados = $produtos_negociados->fetchAll();

$tags_contato = $pdo->prepare("SELECT t.* FROM tags t JOIN contato_tags ct ON t.id = ct.tag_id WHERE ct.contato_id = ?");
$tags_contato->execute([$id]);
$tags_contato = $tags_contato->fetchAll();

$todas_tags = $pdo->query("SELECT * FROM tags ORDER BY nome ASC")->fetchAll();
$todos_produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC")->fetchAll();

// Lead esfriando logic
$esfriando = false;
$dias_inatividade = null;
if (!in_array($contato['status'], ['Fechado', 'Perdido'])) {
    if ($contato['ultima_interacao']) {
        $diff = date_diff(date_create($contato['ultima_interacao']), date_create('now'));
        $dias_inatividade = $diff->days;
        if ($dias_inatividade > 3) $esfriando = true;
    } else {
        $esfriando = true;
    }
}
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h2>
            <?= htmlspecialchars($contato['nome']) ?>
            <span class="badge bg-secondary ms-2" style="font-size: 0.5em; vertical-align: middle;"><?= $contato['status'] ?></span>
            <?php if ($esfriando): ?>
                <span class="badge bg-danger ms-1" style="font-size: 0.5em; vertical-align: middle;"><i class="fas fa-snowflake"></i> Lead Esfriando</span>
            <?php endif; ?>
        </h2>
        <div class="mb-2">
            <?php foreach ($tags_contato as $tc): ?>
                <span class="tag-badge me-1" style="background-color: <?= $tc['cor'] ?>;">
                    <?= htmlspecialchars($tc['nome']) ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="remove_tag">
                        <input type="hidden" name="tag_id" value="<?= $tc['id'] ?>">
                        <button type="submit" class="btn btn-sm p-0 ms-1 text-white border-0 bg-transparent">&times;</button>
                    </form>
                </span>
            <?php endforeach; ?>
            <button class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalAddTag"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <?php if ($contato['telefone']): ?>
            <a href="https://wa.me/55<?= preg_replace('/\D/', '', $contato['telefone']) ?>" target="_blank" class="btn btn-success"><i class="fab fa-whatsapp"></i> Chamar no Whats</a>
        <?php endif; ?>
        <a href="contatos.php" class="btn btn-outline-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <!-- Coluna Esquerda: Dados e Editar -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">Dados do Contato</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="edit_contato">
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Nome</label>
                        <input type="text" name="nome" class="form-control form-control-sm" value="<?= htmlspecialchars($contato['nome']) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Telefone</label>
                        <input type="text" name="telefone" class="form-control form-control-sm" value="<?= htmlspecialchars($contato['telefone']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">E-mail</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($contato['email']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Nicho / Segmento</label>
                        <input type="text" name="nicho" class="form-control form-control-sm" value="<?= htmlspecialchars($contato['nicho']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Cidade</label>
                        <input type="text" name="cidade" class="form-control form-control-sm" value="<?= htmlspecialchars($contato['cidade']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Origem</label>
                        <input type="text" name="origem" class="form-control form-control-sm" value="<?= htmlspecialchars($contato['origem']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <?php $statuses = ['Lead novo', 'Primeiro contato', 'Em negociação', 'Fechado', 'Perdido']; ?>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st ?>" <?= $contato['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-0">Observações</label>
                        <textarea name="observacoes" class="form-control form-control-sm" rows="4"><?= htmlspecialchars($contato['observacoes']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Atualizar Dados</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Coluna Direita: Interações, Tarefas, Produtos -->
    <div class="col-md-8">
        
        <!-- TAREFAS -->
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning fw-bold text-dark d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tasks"></i> Tarefas</span>
                <button class="btn btn-sm btn-dark" data-bs-toggle="collapse" data-bs-target="#boxNovaTarefa"><i class="fas fa-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <div class="collapse p-3 bg-light border-bottom" id="boxNovaTarefa">
                    <form method="POST" class="row g-2">
                        <input type="hidden" name="action" value="add_tarefa">
                        <div class="col-md-7">
                            <input type="text" name="descricao" class="form-control form-control-sm" placeholder="O que precisa ser feito?" required>
                        </div>
                        <div class="col-md-3">
                            <input type="datetime-local" name="data" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-success w-100">Salvar</button>
                        </div>
                    </form>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (!$tarefas): ?>
                        <li class="list-group-item text-muted text-center py-3">Nenhuma tarefa cadastrada.</li>
                    <?php endif; ?>
                    <?php foreach ($tarefas as $t): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center <?= $t['status'] === 'Concluída' ? 'bg-light text-muted' : '' ?>">
                            <div>
                                <?php if ($t['status'] === 'Concluída'): ?>
                                    <i class="fas fa-check-circle text-success me-2"></i> <del><?= htmlspecialchars($t['descricao']) ?></del>
                                <?php else: ?>
                                    <i class="far fa-circle text-warning me-2"></i> <span class="fw-bold"><?= htmlspecialchars($t['descricao']) ?></span>
                                <?php endif; ?>
                                <br><small class="text-muted ms-4"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($t['data'])) ?></small>
                            </div>
                            <?php if ($t['status'] === 'Pendente'): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="concluir_tarefa">
                                    <input type="hidden" name="tarefa_id" value="<?= $t['id'] ?>">
                                    <button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- INTERAÇÕES -->
        <div class="card border-info mb-4">
            <div class="card-header bg-info text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-comments"></i> Interações & Follow-up</span>
                <button class="btn btn-sm btn-dark" data-bs-toggle="collapse" data-bs-target="#boxNovaInteracao"><i class="fas fa-plus"></i> Registrar</button>
            </div>
            <div class="card-body p-0">
                <div class="collapse p-3 bg-light border-bottom" id="boxNovaInteracao">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_interacao">
                        <div class="mb-2">
                            <select name="tipo" class="form-select form-select-sm" required>
                                <option value="follow_up">Follow Up</option>
                                <option value="primeiro_contato">Primeiro Contato (Prospecção Inicial)</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="descricao" class="form-control form-control-sm" rows="3" placeholder="Resumo da conversa..." required></textarea>
                        </div>
                        <button class="btn btn-sm btn-info w-100 text-white">Salvar Interação</button>
                    </form>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (!$interacoes): ?>
                        <li class="list-group-item text-muted text-center py-3">Sem interações registradas.</li>
                    <?php endif; ?>
                    <?php foreach ($interacoes as $int): ?>
                        <li class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <?php if ($int['tipo'] === 'primeiro_contato'): ?>
                                        <span class="badge bg-primary">Primeiro Contato</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Follow up</span>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($int['data'])) ?></small>
                            </div>
                            <p class="mb-1 text-break"><?= nl2br(htmlspecialchars($int['descricao'])) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- PRODUTOS / NEGOCIAÇÃO -->
        <div class="card border-success mb-4">
            <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-box-open"></i> Produtos em Negociação</span>
                <button class="btn btn-sm btn-dark" data-bs-toggle="collapse" data-bs-target="#boxNovoProduto"><i class="fas fa-plus"></i></button>
            </div>
            <div class="card-body p-0">
                <div class="collapse p-3 bg-light border-bottom" id="boxNovoProduto">
                    <?php if($todos_produtos): ?>
                    <form method="POST" class="row g-2 align-items-end">
                        <input type="hidden" name="action" value="add_produto">
                        <div class="col-md-3">
                            <label class="small text-muted">Produto</label>
                            <select name="produto_id" class="form-select form-select-sm" required>
                                <?php foreach($todos_produtos as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-preco="<?= $p['preco_padrao'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">Valor Total (R$)</label>
                            <input type="number" step="0.01" name="preco_negociado" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted">Qtd</label>
                            <input type="number" name="quantidade" value="1" min="1" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">Status</label>
                            <select name="status_produto" class="form-select form-select-sm">
                                <option value="Em negociação">Em negociação</option>
                                <option value="Proposta enviada">Proposta</option>
                                <option value="Fechado">Fechado</option>
                                <option value="Perdido">Perdido</option>
                            </select>
                        </div>
                        <div class="col-md-1 text-end">
                            <button class="btn btn-sm btn-success w-100"><i class="fas fa-save"></i></button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0 p-2 text-center text-sm">Nenhum produto cadastrado no sistema. Vá em "Produtos" no menu lateral.</div>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <?php if ($produtos_negociados): ?>
                            <thead class="table-light">
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Total R$</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos_negociados as $pn): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pn['nome']) ?></td>
                                        <td><?= $pn['quantidade'] ?></td>
                                        <td class="fw-bold text-success">R$ <?= number_format($pn['preco_negociado'], 2, ',', '.') ?></td>
                                        <td>
                                            <form method="POST" class="d-flex">
                                                <input type="hidden" name="action" value="update_produto">
                                                <input type="hidden" name="cp_id" value="<?= $pn['id'] ?>">
                                                <select name="status_produto" class="form-select form-select-sm me-1" onchange="this.form.submit()" style="width:auto;">
                                                    <option value="Em negociação" <?= $pn['status']=='Em negociação'?'selected':'' ?>>Em negociação</option>
                                                    <option value="Proposta enviada" <?= $pn['status']=='Proposta enviada'?'selected':'' ?>>Proposta enviada</option>
                                                    <option value="Fechado" <?= $pn['status']=='Fechado'?'selected':'' ?>>Fechado</option>
                                                    <option value="Perdido" <?= $pn['status']=='Perdido'?'selected':'' ?>>Perdido</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Nenhum produto em negociação.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Adicionar Tag -->
<div class="modal fade" id="modalAddTag" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Adicionar Tag</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_tag">
            <select name="tag_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach($todas_tags as $tg): ?>
                    <option value="<?= $tg['id'] ?>"><?= htmlspecialchars($tg['nome']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Adicionar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
    // Se o usuário selecionar um produto, sugerir o valor base no campo valor total
    document.querySelector('select[name="produto_id"]')?.addEventListener('change', function(e) {
        let opt = this.options[this.selectedIndex];
        let preco = opt.getAttribute('data-preco');
        document.querySelector('input[name="preco_negociado"]').value = preco;
    });
</script>

<?php require_once 'includes/footer.php'; ?>
