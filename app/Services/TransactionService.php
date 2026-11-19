<?php
    class TransactionService {

        public const CATEGORIES = [
            'Vendas',
            'Serviços',
            'Fornecedores',
            'Aluguel',
            'Salários',
            'Impostos',
            'Contas (água, luz, internet)',
            'Marketing',
            'Outros',
        ];

        public function __construct(private TransactionRepository $transactions) {}

        public function listForUser(int $userId, ?string $typeFilter = null): array {
            return $this->transactions->findAllByUser($userId, TransactionType::tryFrom((string) $typeFilter));
        }

        public function summary(int $userId): array {
            return $this->transactions->summary($userId);
        }

        public function find(int $id, int $userId): ?Transaction {
            return $id > 0 ? $this->transactions->findByIdForUser($id, $userId) : null;
        }

        public function create(int $userId, array $input): Transaction {
            $transaction = $this->buildTransaction($userId, $input);
            $transaction->id = $this->transactions->create($transaction);

            return $transaction;
        }

        public function update(Transaction $existing, array $input): Transaction {
            $transaction = $this->buildTransaction($existing->userId, $input);
            $transaction->id = $existing->id;
            $this->transactions->update($transaction);

            return $transaction;
        }

        public function delete(Transaction $transaction): void {
            $this->transactions->delete($transaction->id, $transaction->userId);
        }

        private function buildTransaction(int $userId, array $input): Transaction {
            $description = trim((string) ($input['description'] ?? ''));
            $amount = Validator::normalizeMoney((string) ($input['amount'] ?? ''));
            $type = TransactionType::tryFrom((string) ($input['type'] ?? ''));
            $category = (string) ($input['category'] ?? '');
            $date = trim((string) ($input['transaction_date'] ?? ''));
            $errors = [];

            $descriptionLength = mb_strlen($description);
            if ($descriptionLength < 3 || $descriptionLength > 150) {
                $errors['description'] = 'A descrição deve ter entre 3 e 150 caracteres.';
            }

            if ($amount === null) {
                $errors['amount'] = 'Informe um valor maior que zero (ex.: 150,00).';
            }

            if ($type === null) {
                $errors['type'] = 'Selecione Receita ou Despesa.';
            }

            if (!in_array($category, self::CATEGORIES, true)) {
                $errors['category'] = 'Selecione uma categoria válida.';
            }

            if (!Validator::isDate($date)) {
                $errors['transaction_date'] = 'Informe uma data válida.';
            }

            if ($errors) {
                throw new ValidationException($errors);
            }

            return new Transaction(
                userId: $userId,
                description: $description,
                amount: $amount,
                type: $type,
                category: $category,
                transactionDate: $date
            );
        }
    }
