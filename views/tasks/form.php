<?php
    $isEdit = $task !== null;
    $title = $isEdit ? 'Editar tarefa' : 'Nova tarefa';
    $active = 'dashboard';
    require __DIR__ . '/../layouts/header.php';
?>

        <div class="page-header">
            <div>
                <h1><?= e($title) ?></h1>
                <p class="muted"><?= $isEdit ? 'Altere os dados da tarefa.' : 'Preencha os dados da nova tarefa.' ?></p>
            </div>
            <a href="/dashboard" class="btn btn-ghost">← Voltar</a>
        </div>

        <section class="panel panel-form">
            <?php if ($errors): ?>
                <div class="alert alert-error" role="alert">Corrija os campos destacados.</div>
            <?php endif; ?>

            <form method="POST" action="<?= $isEdit ? '/tasks/update' : '/tasks' ?>">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int) $task->id ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Título</label>
                    <input type="text" id="title" name="title" maxlength="150" required
                           placeholder="Ex.: Conferir estoque do mês"
                           value="<?= e($old['title'] ?? '') ?>">
                    <?php if (!empty($errors['title'])): ?>
                        <small class="field-error"><?= e($errors['title']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">Descrição <span class="muted">(opcional)</span></label>
                    <textarea id="description" name="description" rows="4" maxlength="2000"
                              placeholder="Detalhes da tarefa"><?= e($old['description'] ?? '') ?></textarea>
                    <?php if (!empty($errors['description'])): ?>
                        <small class="field-error"><?= e($errors['description']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <?php if ($task !== null): ?>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <?php foreach (TaskStatus::cases() as $status): ?>
                                <option value="<?= e($status->value) ?>"
                                    <?= ($old['status'] ?? '') === $status->value ? 'selected' : '' ?>>
                                    <?= e($status->label()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['status'])): ?>
                            <small class="field-error"><?= e($errors['status']) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="due_date">Prazo <span class="muted">(opcional)</span></label>
                        <input type="date" id="due_date" name="due_date"
                               value="<?= e($old['due_date'] ?? '') ?>">
                        <?php if (!empty($errors['due_date'])): ?>
                            <small class="field-error"><?= e($errors['due_date']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <fieldset class="form-section">
                    <legend>Lançamento financeiro <span class="muted">(opcional)</span></legend>

                    <?php if ($isEdit && $task->isSettled()): ?>
                        <p class="form-hint">
                            <span class="badge badge-<?= strtolower(e($task->transactionType->value)) ?>"><?= e($task->transactionType->label()) ?></span>
                            <strong><?= e(money($task->amount)) ?></strong> em <?= e($task->category) ?>
                            — já registrado em Lançamentos.
                            <a href="/transactions/edit?id=<?= (int) $task->transactionId ?>">Ver lançamento</a>
                        </p>
                        <p class="form-hint muted">Para corrigir o valor, edite o lançamento. Se a tarefa sair de "Concluída", o lançamento é excluído.</p>
                    <?php else: ?>
                        <p class="form-hint muted">Preencha se esta tarefa envolve dinheiro (ex.: pagar um imposto). Ao concluir a tarefa, o lançamento é registrado automaticamente.</p>

                        <div class="form-group">
                            <div class="radio-group">
                                <?php foreach (TransactionType::cases() as $type): ?>
                                    <label class="radio radio-<?= strtolower(e($type->value)) ?>">
                                        <input type="radio" name="transaction_type" value="<?= e($type->value) ?>"
                                            <?= ($old['transaction_type'] ?? '') === $type->value ? 'checked' : '' ?>>
                                        <?= e($type->label()) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($errors['transaction_type'])): ?>
                                <small class="field-error"><?= e($errors['transaction_type']) ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="amount">Valor (R$)</label>
                                <input type="text" id="amount" name="amount" inputmode="decimal"
                                       placeholder="Ex.: 1.000,00"
                                       value="<?= e($old['amount'] ?? '') ?>">
                                <?php if (!empty($errors['amount'])): ?>
                                    <small class="field-error"><?= e($errors['amount']) ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="category">Categoria</label>
                                <select id="category" name="category">
                                    <option value="">Selecione...</option>
                                    <?php foreach (TransactionService::CATEGORIES as $category): ?>
                                        <option value="<?= e($category) ?>"
                                            <?= ($old['category'] ?? '') === $category ? 'selected' : '' ?>>
                                            <?= e($category) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($errors['category'])): ?>
                                    <small class="field-error"><?= e($errors['category']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </fieldset>

                <div class="form-actions">
                    <a href="/dashboard" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar alterações' : 'Adicionar tarefa' ?></button>
                </div>
            </form>
        </section>
