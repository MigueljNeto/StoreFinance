<?php
    class UserRepository {
        public function __construct(private PDO $pdo) {}

        public function findByName(string $name): ?Users {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM users WHERE name = :name LIMIT 1"
            );

            $stmt->execute(['name' => $name]);

            return $this->hydrate($stmt->fetch());
        }

        public function findByEmail(string $email): ?Users {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM users WHERE email = :email LIMIT 1"
            );

            $stmt->execute(['email' => $email]);

            return $this->hydrate($stmt->fetch());
        }

        public function findById(int $id): ?Users {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM users WHERE id = :id LIMIT 1"
            );

            $stmt->execute(['id' => $id]);

            return $this->hydrate($stmt->fetch());
        }

        public function emailExists(string $email): bool {
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM users WHERE email = :email LIMIT 1"
            );

            $stmt->execute(['email' => $email]);

            return (bool) $stmt->fetchColumn();
        }

        public function createUser(Users $users): int {
            $stmt = $this->pdo->prepare(
                "INSERT INTO users
                (
                    name,
                    email,
                    password,
                    active
                ) VALUES (
                    :name,
                    :email,
                    :password,
                    :active
                )
             ");

             $stmt->execute([
                'name' => $users->name,
                'email' => $users->email,
                'password' => $users->password,
                'active' => (int) $users->active,
             ]);

             return (int) $this->pdo->lastInsertId();
        }

        public function updatePassword(int $id, string $hash): void {
            $stmt = $this->pdo->prepare(
                "UPDATE users SET password = :password WHERE id = :id"
            );

            $stmt->execute(['password' => $hash, 'id' => $id]);
        }

        private function hydrate(array|false $row): ?Users {
            if (!$row) {
                return null;
            }

            return new Users(
                name: $row['name'],
                email: (string) $row['email'],
                active: (bool) $row['active'],
                id: (int) $row['id'],
                createdAt: $row['created_at'],
                password: $row['password']
            );
        }
    }
