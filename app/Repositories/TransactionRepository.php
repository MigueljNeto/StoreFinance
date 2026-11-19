<?php
    class TransactionRepository {
        public function __construct(private PDO $pdo) {}

        public function findAllByUser(int $userId, ?TransactionType $type = null): array {
            $sql = "SELECT * FROM transactions WHERE user_id = :user_id";
            $params = ['user_id' => $userId];

            if ($type !== null) {
                $sql .= " AND type = :type";
                $params['type'] = $type->value;
            }

            $sql .= " ORDER BY transaction_date DESC, id DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return array_map(fn(array $row) => $this->hydrate($row), $stmt->fetchAll());
        }

        public function findByIdForUser(int $id, int $userId): ?Transaction {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM transactions WHERE id = :id AND user_id = :user_id LIMIT 1"
            );

            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            $row = $stmt->fetch();

            return $row ? $this->hydrate($row) : null;
        }

        public function summary(int $userId): array {
            $stmt = $this->pdo->prepare(
                "SELECT
                    COALESCE(SUM(CASE WHEN type = 'INCOME' THEN amount END), 0) AS income,
                    COALESCE(SUM(CASE WHEN type = 'EXPENSE' THEN amount END), 0) AS expense,
                    COALESCE(SUM(CASE WHEN type = 'INCOME' THEN amount ELSE -amount END), 0) AS balance
                 FROM transactions
                 WHERE user_id = :user_id"
            );

            $stmt->execute(['user_id' => $userId]);
            $row = $stmt->fetch();

            return [
                'income' => (string) $row['income'],
                'expense' => (string) $row['expense'],
                'balance' => (string) $row['balance'],
            ];
        }

        public function create(Transaction $transaction): int {
            $stmt = $this->pdo->prepare(
                "INSERT INTO transactions (user_id, description, amount, type, category, transaction_date)
                 VALUES (:user_id, :description, :amount, :type, :category, :transaction_date)"
            );

            $stmt->execute([
                'user_id' => $transaction->userId,
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'type' => $transaction->type->value,
                'category' => $transaction->category,
                'transaction_date' => $transaction->transactionDate,
            ]);

            return (int) $this->pdo->lastInsertId();
        }

        public function update(Transaction $transaction): bool {
            $stmt = $this->pdo->prepare(
                "UPDATE transactions
                 SET description = :description, amount = :amount, type = :type,
                     category = :category, transaction_date = :transaction_date
                 WHERE id = :id AND user_id = :user_id"
            );

            $stmt->execute([
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'type' => $transaction->type->value,
                'category' => $transaction->category,
                'transaction_date' => $transaction->transactionDate,
                'id' => $transaction->id,
                'user_id' => $transaction->userId,
            ]);

            return $stmt->rowCount() > 0;
        }

        public function delete(int $id, int $userId): bool {
            $stmt = $this->pdo->prepare(
                "DELETE FROM transactions WHERE id = :id AND user_id = :user_id"
            );

            $stmt->execute(['id' => $id, 'user_id' => $userId]);

            return $stmt->rowCount() > 0;
        }

        private function hydrate(array $row): Transaction {
            return new Transaction(
                userId: (int) $row['user_id'],
                description: $row['description'],
                amount: (string) $row['amount'],
                type: TransactionType::from($row['type']),
                category: $row['category'],
                transactionDate: $row['transaction_date'],
                id: (int) $row['id'],
                createdAt: $row['created_at']
            );
        }
    }
