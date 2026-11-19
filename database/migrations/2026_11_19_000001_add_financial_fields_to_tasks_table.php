<?php

return new class {
    public function up(PDO $pdo): void {
        $pdo->exec("
            ALTER TABLE tasks
                ADD COLUMN amount DECIMAL(12,2) NULL AFTER due_date,
                ADD COLUMN transaction_type VARCHAR(10) NULL AFTER amount,
                ADD COLUMN category VARCHAR(60) NULL AFTER transaction_type,
                ADD COLUMN transaction_id BIGINT UNSIGNED NULL AFTER category,
                ADD CONSTRAINT fk_tasks_transaction
                    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("ALTER TABLE tasks DROP FOREIGN KEY fk_tasks_transaction");
        $pdo->exec("
            ALTER TABLE tasks
                DROP COLUMN transaction_id,
                DROP COLUMN category,
                DROP COLUMN transaction_type,
                DROP COLUMN amount
        ");
    }
};
