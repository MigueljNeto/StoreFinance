<?php
    $isEdit = $transaction !== null;
    $title = $isEdit ? 'Editar lançamento' : 'Novo lançamento';
    $active = 'transactions';
    require __DIR__ . '/../layouts/header.php';
?>

        <div class="page-header">
            <div>
                <h1><?= e($title) ?></h1>
                <p class="muted">Registre uma receita ou despesa da loja.</p>
            </div>
            <a href="/transactions" class="btn btn-ghost">← Voltar</a>
        </div>

        <section class="panel panel-form">
            <?php if ($errors): ?>
                <div class="alert alert-error" role="alert">Corrija os campos destacados.</div>
            <?php endif; ?>

            <form method="POST" action="<?= $isEdit ? '/transactions/update' : '/transactions' ?>">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int) $transaction->id ?>">
                <?php endif; ?>

                <div class="form-group">
                    <span class="label">Tipo</span>
                    <div class="radio-group">
                        <?php foreach (TransactionType::cases() as $type): ?>
                            <label class="radio radio-<?= strtolower(e($type->value)) ?>">
                                <input type="radio" name="type" value="<?= e($type->value) ?>" required
                                    <?= ($old['type'] ?? '') === $type->value ? 'checked' : '' ?>>
                                <?= e($type->label()) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($errors['type'])): ?>
                        <small class="field-error"><?= e($errors['type']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">Descrição</label>
                    <input type="text" id="description" name="description" maxlength="150" required
                           placeholder="Ex.: Venda balcão, Conta de luz"
                           value="<?= e($old['description'] ?? '') ?>">
                    <?php if (!empty($errors['description'])): ?>
                        <small class="field-error"><?= e($errors['description']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="amount">Valor (R$)</label>
                        <input type="text" id="amount" name="amount" inputmode="decimal" required
                               placeholder="0,00"
                               value="<?= e($old['amount'] ?? '') ?>">
                        <?php if (!empty($errors['amount'])): ?>
                            <small class="field-error"><?= e($errors['amount']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="transaction_date">Data</label>
                        <input type="date" id="transaction_date" name="transaction_date" required
                               value="<?= e($old['transaction_date'] ?? '') ?>">
                        <?php if (!empty($errors['transaction_date'])): ?>
                            <small class="field-error"><?= e($errors['transaction_date']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category">Categoria</label>
                    <select id="category" name="category" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categories as $category): ?>
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

                <div class="form-actions">
                    <a href="/transactions" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar alterações' : 'Cadastrar lançamento' ?></button>
                </div>
            </form>
        </section>
