<?php
    enum TransactionType: string {
        case INCOME = 'INCOME';
        case EXPENSE = 'EXPENSE';

        public function label(): string {
            return match ($this) {
                self::INCOME => 'Receita',
                self::EXPENSE => 'Despesa',
            };
        }
    }
