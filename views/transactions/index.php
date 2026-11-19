<?php
    $title = 'Lançamentos';
    $active = 'transactions';
    require __DIR__ . '/../layouts/header.php';
?>

        <div class="page-header">
            <div>
                <h1>Lançamentos financeiros</h1>
                <p class="muted">Controle as receitas e despesas da sua loja.</p>
            </div>
            <a href="/transactions/create" class="btn btn-primary">+ Novo lançamento</a>
        </div>

        <section class="cards cards-3">
            <div class="card">
                <span class="card-label">Receitas</span>
                <strong class="card-value positive"><?= e(money($summary['income'])) ?></strong>
            </div>
            <div class="card">
                <span class="card-label">Despesas</span>
                <strong class="card-value negative"><?= e(money($summary['expense'])) ?></strong>
            </div>
            <div class="card card-highlight">
                <span class="card-label">Saldo</span>
                <strong class="card-value <?= (float) $summary['balance'] < 0 ? 'negative' : 'positive' ?>">
                    <?= e(money($summary['balance'])) ?>
                </strong>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Histórico</h2>

                <nav class="filters">
                    <a href="/transactions" class="<?= $currentType === null ? 'active' : '' ?>">Todos</a>
                    <?php foreach (TransactionType::cases() as $type): ?>
                        <a href="/transactions?type=<?= e($type->value) ?>"
                           class="<?= $currentType === $type ? 'active' : '' ?>">
                            <?= e($type->label()) ?>s
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <?php if (!$transactions): ?>
                <div class="empty">
                    <p>Nenhum lançamento cadastrado.</p>
                    <a href="/transactions/create" class="btn btn-primary">Cadastrar lançamento</a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th class="text-right">Valor</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <?php $isIncome = $transaction->type === TransactionType::INCOME; ?>
                                <tr>
                                    <td><?= e(dateBr($transaction->transactionDate)) ?></td>
                                    <td><?= e($transaction->description) ?></td>
                                    <td><?= e($transaction->category) ?></td>
                                    <td>
                                        <span class="badge <?= $isIncome ? 'badge-income' : 'badge-expense' ?>">
                                            <?= e($transaction->type->label()) ?>
                                        </span>
                                    </td>
                                    <td class="text-right <?= $isIncome ? 'positive' : 'negative' ?>">
                                        <?= $isIncome ? '+' : '−' ?> <?= e(money($transaction->amount)) ?>
                                    </td>
                                    <td class="text-right">
                                        <div class="actions">
                                            <a href="/transactions/edit?id=<?= (int) $transaction->id ?>" class="btn btn-ghost btn-sm">Editar</a>
                                            <form method="POST" action="/transactions/delete"
                                                  onsubmit="return confirm('Excluir este lançamento?');">
                                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $transaction->id ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
