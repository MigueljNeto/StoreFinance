<?php

return new class {
    public function up(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(150) NOT NULL,
                description TEXT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
                due_date DATE NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tasks_user_status (user_id, status),
                CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS tasks");
    }
};
