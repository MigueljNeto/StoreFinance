<?php
    class TaskService {

        public function __construct(
            private TaskRepository $tasks,
            private TransactionService $transactions,
            private PDO $pdo
        ) {}

        public function listForUser(int $userId, ?string $statusFilter = null, ?string $dueDate = null): array {
            return $this->tasks->findAllByUser($userId, TaskStatus::tryFrom((string) $statusFilter), $dueDate);
        }

        public function countByStatus(int $userId): array {
            return $this->tasks->countByStatus($userId);
        }

        public function calendar(int $userId, ?string $month, ?string $selectedDate = null): array {
            $current = self::parseMonth($month)
                ?? ($selectedDate !== null ? self::parseMonth(substr($selectedDate, 0, 7)) : null)
                ?? new DateTimeImmutable('first day of this month midnight');

            $tasks = $this->tasks->findByUserBetween(
                $userId,
                CalendarBuilder::gridStart($current)->format('Y-m-d'),
                CalendarBuilder::gridEnd($current)->format('Y-m-d')
            );

            return [
                'month' => $current->format('Y-m'),
                'monthLabel' => CalendarBuilder::monthLabel($current),
                'weekdays' => CalendarBuilder::WEEKDAYS,
                'weeks' => (new CalendarBuilder())->build($current, $tasks),
                'prevMonth' => $current->modify('-1 month')->format('Y-m'),
                'nextMonth' => $current->modify('+1 month')->format('Y-m'),
                'isCurrentMonth' => $current->format('Y-m') === date('Y-m'),
            ];
        }

        private static function parseMonth(?string $month): ?DateTimeImmutable {
            if ($month === null || !preg_match('/^\d{4}-\d{2}$/', $month)) {
                return null;
            }

            $date = DateTimeImmutable::createFromFormat('!Y-m', $month);

            return $date && $date->format('Y-m') === $month ? $date : null;
        }

        public function find(int $id, int $userId): ?Task {
            return $id > 0 ? $this->tasks->findByIdForUser($id, $userId) : null;
        }

        public function create(int $userId, array $input): Task {
            $input['status'] = TaskStatus::PENDING->value;
            $task = $this->buildTask($userId, $input);
            $task->id = $this->tasks->create($task);

            return $task;
        }

        public function update(Task $existing, array $input): Task {
            $task = $this->buildTask($existing->userId, $input);
            $task->id = $existing->id;
            $task->transactionId = $existing->transactionId;

            if ($existing->isSettled()) {
                $task->amount = $existing->amount;
                $task->transactionType = $existing->transactionType;
                $task->category = $existing->category;
            }

            $this->save($task);

            return $task;
        }

        public function start(Task $task): bool {
            if (!$task->status->canStart()) {
                return false;
            }

            return $this->tasks->updateStatus($task->id, $task->userId, TaskStatus::IN_PROGRESS);
        }

        public function complete(Task $task): void {
            if ($task->status !== TaskStatus::DONE) {
                $task->status = TaskStatus::DONE;
                $this->save($task);
            }
        }

        public function reopen(Task $task): void {
            if ($task->status === TaskStatus::DONE) {
                $task->status = TaskStatus::PENDING;
                $this->save($task);
            }
        }

        public function toggle(Task $task): void {
            $task->status === TaskStatus::DONE ? $this->reopen($task) : $this->complete($task);
        }

        private function save(Task $task): void {
            $this->pdo->beginTransaction();

            try {
                $this->syncTransaction($task);
                $this->tasks->update($task);
                $this->pdo->commit();
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        private function syncTransaction(Task $task): void {
            $isDone = $task->status === TaskStatus::DONE;

            if ($isDone && $task->hasFinancial() && !$task->isSettled()) {
                $transaction = $this->transactions->create($task->userId, [
                    'description' => $task->title,
                    'amount' => $task->amount,
                    'type' => $task->transactionType->value,
                    'category' => $task->category,
                    'transaction_date' => date('Y-m-d'),
                ]);

                $task->transactionId = $transaction->id;
            } elseif (!$isDone && $task->isSettled()) {
                $transaction = $this->transactions->find($task->transactionId, $task->userId);

                if ($transaction !== null) {
                    $this->transactions->delete($transaction);
                }

                $task->transactionId = null;
            }
        }

        public function delete(Task $task): void {
            $this->tasks->delete($task->id, $task->userId);
        }

        private function buildTask(int $userId, array $input): Task {
            $title = trim((string) ($input['title'] ?? ''));
            $description = trim((string) ($input['description'] ?? ''));
            $status = TaskStatus::tryFrom((string) ($input['status'] ?? ''));
            $dueDate = trim((string) ($input['due_date'] ?? ''));
            $amountInput = trim((string) ($input['amount'] ?? ''));
            $amount = null;
            $transactionType = null;
            $category = null;
            $errors = [];

            $titleLength = mb_strlen($title);
            if ($titleLength < 3 || $titleLength > 150) {
                $errors['title'] = 'O título deve ter entre 3 e 150 caracteres.';
            }

            if (mb_strlen($description) > 2000) {
                $errors['description'] = 'A descrição pode ter no máximo 2000 caracteres.';
            }

            if ($status === null) {
                $errors['status'] = 'Selecione um status válido.';
            }

            if ($dueDate !== '' && !Validator::isDate($dueDate)) {
                $errors['due_date'] = 'Informe uma data válida.';
            }

            if ($amountInput !== '') {
                $amount = Validator::normalizeMoney($amountInput);
                $transactionType = TransactionType::tryFrom((string) ($input['transaction_type'] ?? ''));
                $category = (string) ($input['category'] ?? '');

                if ($amount === null) {
                    $errors['amount'] = 'Informe um valor válido maior que zero.';
                }

                if ($transactionType === null) {
                    $errors['transaction_type'] = 'Escolha se é receita ou despesa.';
                }

                if (!in_array($category, TransactionService::CATEGORIES, true)) {
                    $errors['category'] = 'Selecione uma categoria válida.';
                }
            }

            if ($errors) {
                throw new ValidationException($errors);
            }

            return new Task(
                userId: $userId,
                title: $title,
                description: $description !== '' ? $description : null,
                status: $status,
                dueDate: $dueDate !== '' ? $dueDate : null,
                amount: $amount,
                transactionType: $transactionType,
                category: $category
            );
        }
    }
