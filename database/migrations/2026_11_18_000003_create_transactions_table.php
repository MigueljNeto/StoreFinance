<?php

return new class {
    public function up(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                description VARCHAR(150) NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                type VARCHAR(10) NOT NULL,
                category VARCHAR(60) NOT NULL,
                transaction_date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_transactions_user_date (user_id, transaction_date),
                CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS transactions");
    }
};
