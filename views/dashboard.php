<?php
    $title = 'Dashboard';
    $active = 'dashboard';
    require __DIR__ . '/layouts/header.php';

    $filters = [
        'status' => $currentStatus?->value,
        'date' => $selectedDate,
        'month' => $calendar['month'],
    ];
    $dashboardUrl = function (array $changes = []) use ($filters): string {
        $query = array_filter(array_merge($filters, $changes), fn($v) => $v !== null && $v !== '');

        return '/dashboard' . ($query ? '?' . http_build_query($query) : '');
    };
    $newTaskUrl = '/tasks/create' . ($selectedDate ? '?due_date=' . urlencode($selectedDate) : '');
?>

        <div class="page-header">
            <div>
                <h1>Olá, <?= e($user->name) ?>!</h1>
                <p class="muted">Acompanhe suas tarefas e as finanças da loja.</p>
            </div>
            <a href="<?= e($newTaskUrl) ?>" class="btn btn-primary">+ Nova tarefa</a>
        </div>

        <section class="cards">
            <a href="/dashboard?status=PENDING" class="card">
                <span class="card-label">Pendentes</span>
                <strong class="card-value"><?= (int) $counts['PENDING'] ?></strong>
            </a>
            <a href="/dashboard?status=IN_PROGRESS" class="card">
                <span class="card-label">Em andamento</span>
                <strong class="card-value"><?= (int) $counts['IN_PROGRESS'] ?></strong>
            </a>
            <a href="/dashboard?status=DONE" class="card">
                <span class="card-label">Concluídas</span>
                <strong class="card-value"><?= (int) $counts['DONE'] ?></strong>
            </a>
            <a href="/transactions" class="card card-highlight">
                <span class="card-label">Saldo da loja</span>
                <strong class="card-value <?= (float) $summary['balance'] < 0 ? 'negative' : 'positive' ?>">
                    <?= e(money($summary['balance'])) ?>
                </strong>
                <span class="card-link">Ver lançamentos →</span>
            </a>
        </section>

        <div class="tasks-layout">
        <section class="panel">
            <div class="panel-header">
                <h2>
                    Minhas tarefas
                    <?php if ($selectedDate): ?>
                        <a href="<?= e($dashboardUrl(['date' => null])) ?>" class="date-chip" title="Remover filtro de dia">
                            Prazo <?= e(dateBr($selectedDate)) ?> ✕
                        </a>
                    <?php endif; ?>
                </h2>

                <nav class="filters">
                    <a href="<?= e($dashboardUrl(['status' => null])) ?>" class="<?= $currentStatus === null ? 'active' : '' ?>">Todas</a>
                    <?php foreach (TaskStatus::cases() as $status): ?>
                        <a href="<?= e($dashboardUrl(['status' => $status->value])) ?>"
                           class="<?= $currentStatus === $status ? 'active' : '' ?>">
                            <?= e($status->label()) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <?php if (!$tasks): ?>
                <div class="empty">
                    <?php if ($selectedDate): ?>
                        <p>Nenhuma tarefa com prazo em <?= e(dateBr($selectedDate)) ?>.</p>
                        <a href="<?= e($newTaskUrl) ?>" class="btn btn-primary">Criar tarefa para este dia</a>
                    <?php else: ?>
                        <p>Nenhuma tarefa por aqui.</p>
                        <a href="/tasks/create" class="btn btn-primary">Criar minha primeira tarefa</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <li class="task <?= $task->status === TaskStatus::DONE ? 'task-done' : '' ?>">
                            <form method="POST" action="/tasks/toggle"
                                  <?= $task->status === TaskStatus::DONE && $task->isSettled()
                                        ? "onsubmit=\"return confirm('Reabrir a tarefa vai excluir o lançamento gerado por ela. Continuar?');\""
                                        : '' ?>>
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="id" value="<?= (int) $task->id ?>">
                                <button type="submit" class="check"
                                        title="<?= $task->status === TaskStatus::DONE
                                            ? 'Reabrir tarefa'
                                            : ($task->hasFinancial() ? 'Concluir e registrar ' . mb_strtolower($task->transactionType->label()) : 'Marcar como concluída') ?>">
                                    <?= $task->status === TaskStatus::DONE ? '✓' : '' ?>
                                </button>
                            </form>

                            <div class="task-body">
                                <strong class="task-title"><?= e($task->title) ?></strong>
                                <?php if ($task->description): ?>
                                    <p class="task-description"><?= nl2br(e($task->description)) ?></p>
                                <?php endif; ?>
                                <div class="task-meta">
                                    <span class="badge badge-<?= strtolower(e($task->status->value)) ?>"><?= e($task->status->label()) ?></span>
                                    <?php if ($task->hasFinancial()): ?>
                                        <span class="badge badge-<?= strtolower(e($task->transactionType->value)) ?>">
                                            <?= e($task->transactionType->label()) ?> · <?= e(money($task->amount)) ?>
                                        </span>
                                        <?php if ($task->isSettled()): ?>
                                            <a href="/transactions/edit?id=<?= (int) $task->transactionId ?>" class="task-link">✓ Lançado · ver lançamento</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($task->dueDate): ?>
                                        <span class="<?= $task->isOverdue() ? 'overdue' : '' ?>">
                                            Prazo: <?= e(dateBr($task->dueDate)) ?><?= $task->isOverdue() ? ' (atrasada)' : '' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="actions">
                                <?php if ($task->status->canStart()): ?>
                                    <form method="POST" action="/tasks/start">
                                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $task->id ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Iniciar</button>
                                    </form>
                                <?php endif; ?>
                                <a href="/tasks/edit?id=<?= (int) $task->id ?>" class="btn btn-ghost btn-sm">Editar</a>
                                <form method="POST" action="/tasks/delete"
                                      onsubmit="return confirm('Excluir esta tarefa?');">
                                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $task->id ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <aside class="panel mini-calendar" aria-label="Calendário de prazos">
            <div class="mini-calendar-header">
                <a href="<?= e($dashboardUrl(['month' => $calendar['prevMonth']])) ?>" class="mini-calendar-arrow" aria-label="Mês anterior">‹</a>
                <strong><?= e($calendar['monthLabel']) ?></strong>
                <a href="<?= e($dashboardUrl(['month' => $calendar['nextMonth']])) ?>" class="mini-calendar-arrow" aria-label="Próximo mês">›</a>
            </div>

            <div class="mini-calendar-grid">
                <?php foreach ($calendar['weekdays'] as $weekday): ?>
                    <span class="mini-calendar-weekday"><?= e($weekday) ?></span>
                <?php endforeach; ?>

                <?php foreach ($calendar['weeks'] as $week): ?>
                    <?php foreach ($week as $day): ?>
                        <?php
                            $isSelected = $day['date'] === $selectedDate;
                            $statuses = array_unique(array_map(fn($t) => $t->status->value, $day['tasks']));
                            $hasOverdue = (bool) array_filter($day['tasks'], fn($t) => $t->isOverdue());
                            $count = count($day['tasks']);
                            $classes = 'mini-day'
                                . ($day['inMonth'] ? '' : ' is-outside')
                                . ($day['isToday'] ? ' is-today' : '')
                                . ($isSelected ? ' is-selected' : '')
                                . ($hasOverdue ? ' is-overdue' : '');
                            $label = dateBr($day['date']) . ($count ? " — {$count} tarefa" . ($count > 1 ? 's' : '') : '');
                        ?>
                        <a href="<?= e($dashboardUrl(['date' => $isSelected ? null : $day['date']])) ?>"
                           class="<?= $classes ?>" title="<?= e($label) ?>"
                           <?= $isSelected ? 'aria-current="date"' : '' ?>>
                            <span><?= (int) $day['day'] ?></span>
                            <?php if ($statuses): ?>
                                <span class="mini-dots">
                                    <?php foreach ($statuses as $statusValue): ?>
                                        <i class="dot dot-<?= strtolower(e($statusValue)) ?>"></i>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <?php if (!$calendar['isCurrentMonth']): ?>
                <a href="<?= e($dashboardUrl(['month' => date('Y-m')])) ?>" class="mini-calendar-today">Voltar para hoje</a>
            <?php endif; ?>
            <p class="mini-calendar-hint muted">Clique num dia para ver as tarefas com aquele prazo.</p>
        </aside>
        </div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
