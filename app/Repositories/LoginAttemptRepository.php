<?php
    class LoginAttemptRepository {
        public function __construct(private PDO $pdo) {}

        public function record(string $email, string $ip): void {
            $stmt = $this->pdo->prepare(
                "INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)"
            );

            $stmt->execute(['email' => $email, 'ip' => $ip]);
        }

        public function countRecentByEmail(string $email, int $minutes): int {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE email = :email
                   AND attempted_at > (NOW() - INTERVAL :minutes MINUTE)"
            );

            $stmt->bindValue('email', $email);
            $stmt->bindValue('minutes', $minutes, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        }

        public function countRecentByIp(string $ip, int $minutes): int {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE ip_address = :ip
                   AND attempted_at > (NOW() - INTERVAL :minutes MINUTE)"
            );

            $stmt->bindValue('ip', $ip);
            $stmt->bindValue('minutes', $minutes, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        }

        public function clearForEmail(string $email): void {
            $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE email = :email");
            $stmt->execute(['email' => $email]);
        }
    }
