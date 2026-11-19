<?php
    class TaskRepository {
        public function __construct(private PDO $pdo) {}

        public function findAllByUser(int $userId, ?TaskStatus $status = null, ?string $dueDate = null): array {
            $sql = "SELECT * FROM tasks WHERE user_id = :user_id";
            $params = ['user_id' => $userId];

            if ($status !== null) {
                $sql .= " AND status = :status";
                $params['status'] = $status->value;
            }

            if ($dueDate !== null) {
                $sql .= " AND due_date = :due_date";
                $params['due_date'] = $dueDate;
            }

            $sql .= " ORDER BY (status = 'DONE'), due_date IS NULL, due_date, id DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return array_map(fn(array $row) => $this->hydrate($row), $stmt->fetchAll());
        }

        public function findByUserBetween(int $userId, string $start, string $end): array {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM tasks
                 WHERE user_id = :user_id AND due_date BETWEEN :start AND :end
                 ORDER BY due_date, (status = 'DONE'), id"
            );

            $stmt->execute(['user_id' => $userId, 'start' => $start, 'end' => $end]);

            return array_map(fn(array $row) => $this->hydrate($row), $stmt->fetchAll());
        }

        public function findByIdForUser(int $id, int $userId): ?Task {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1"
            );

            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            $row = $stmt->fetch();

            return $row ? $this->hydrate($row) : null;
        }

        public function countByStatus(int $userId): array {
            $stmt = $this->pdo->prepare(
                "SELECT status, COUNT(*) AS total FROM tasks WHERE user_id = :user_id GROUP BY status"
            );

            $stmt->execute(['user_id' => $userId]);

            $counts = array_fill_keys(array_column(TaskStatus::cases(), 'value'), 0);

            foreach ($stmt->fetchAll() as $row) {
                $counts[$row['status']] = (int) $row['total'];
            }

            return $counts;
        }

        public function create(Task $task): int {
            $stmt = $this->pdo->prepare(
                "INSERT INTO tasks (user_id, title, description, status, due_date,
                                    amount, transaction_type, category, transaction_id)
                 VALUES (:user_id, :title, :description, :status, :due_date,
                         :amount, :transaction_type, :category, :transaction_id)"
            );

            $stmt->execute([
                'user_id' => $task->userId,
                ...$this->columns($task),
            ]);

            return (int) $this->pdo->lastInsertId();
        }

        public function update(Task $task): bool {
            $stmt = $this->pdo->prepare(
                "UPDATE tasks
                 SET title = :title, description = :description, status = :status, due_date = :due_date,
                     amount = :amount, transaction_type = :transaction_type, category = :category,
                     transaction_id = :transaction_id
                 WHERE id = :id AND user_id = :user_id"
            );

            $stmt->execute([
                ...$this->columns($task),
                'id' => $task->id,
                'user_id' => $task->userId,
            ]);

            return $stmt->rowCount() > 0;
        }

        public function updateStatus(int $id, int $userId, TaskStatus $status): bool {
            $stmt = $this->pdo->prepare(
                "UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id"
            );

            $stmt->execute(['status' => $status->value, 'id' => $id, 'user_id' => $userId]);

            return $stmt->rowCount() > 0;
        }

        public function delete(int $id, int $userId): bool {
            $stmt = $this->pdo->prepare(
                "DELETE FROM tasks WHERE id = :id AND user_id = :user_id"
            );

            $stmt->execute(['id' => $id, 'user_id' => $userId]);

            return $stmt->rowCount() > 0;
        }

        private function columns(Task $task): array {
            return [
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'due_date' => $task->dueDate,
                'amount' => $task->amount,
                'transaction_type' => $task->transactionType?->value,
                'category' => $task->category,
                'transaction_id' => $task->transactionId,
            ];
        }

        private function hydrate(array $row): Task {
            return new Task(
                userId: (int) $row['user_id'],
                title: $row['title'],
                description: $row['description'],
                status: TaskStatus::from($row['status']),
                dueDate: $row['due_date'],
                id: (int) $row['id'],
                createdAt: $row['created_at'],
                amount: $row['amount'],
                transactionType: TransactionType::tryFrom((string) $row['transaction_type']),
                category: $row['category'],
                transactionId: $row['transaction_id'] !== null ? (int) $row['transaction_id'] : null
            );
        }
    }
