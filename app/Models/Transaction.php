<?php
    class Transaction {
        public function __construct(
            public int $userId,
            public string $description,
            public string $amount,
            public TransactionType $type,
            public string $category,
            public string $transactionDate,
            public ?int $id = null,
            public ?string $createdAt = null
        ) {}
    }
