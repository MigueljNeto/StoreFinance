<?php

return new class {
    public function up(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(150) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_login_attempts_email (email, attempted_at),
                INDEX idx_login_attempts_ip (ip_address, attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE IF EXISTS login_attempts");
    }
};
