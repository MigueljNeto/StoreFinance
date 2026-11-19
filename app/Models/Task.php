<?php
    class Task {
        public function __construct(
            public int $userId,
            public string $title,
            public ?string $description = null,
            public TaskStatus $status = TaskStatus::PENDING,
            public ?string $dueDate = null,
            public ?int $id = null,
            public ?string $createdAt = null,
            public ?string $amount = null,
            public ?TransactionType $transactionType = null,
            public ?string $category = null,
            public ?int $transactionId = null
        ) {}

        public function isOverdue(): bool {
            return $this->status !== TaskStatus::DONE
                && $this->dueDate !== null
                && $this->dueDate < date('Y-m-d');
        }

        public function hasFinancial(): bool {
            return $this->amount !== null && $this->transactionType !== null && $this->category !== null;
        }

        public function isSettled(): bool {
            return $this->transactionId !== null;
        }
    }
