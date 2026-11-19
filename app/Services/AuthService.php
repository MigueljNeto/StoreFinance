<?php
    class AuthService {

        private const MAX_ATTEMPTS_PER_EMAIL = 5;
        private const MAX_ATTEMPTS_PER_IP = 20;
        private const LOCKOUT_MINUTES = 15;
        private const MIN_PASSWORD_LENGTH = 8;
        private const MAX_PASSWORD_BYTES = 72; 

        public function __construct(
            private UserRepository $users,
            private LoginAttemptRepository $attempts
        ) {}

        public function register(string $name, string $email, string $password, string $confirmation): array {
            $name = trim($name);
            $email = $this->normalizeEmail($email);
            $errors = [];

            $nameLength = mb_strlen($name);
            if ($nameLength < 2 || $nameLength > 120) {
                $errors['name'] = 'O nome deve ter entre 2 e 120 caracteres.';
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 150) {
                $errors['email'] = 'Informe um e-mail válido.';
            }

            if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $errors['password'] = 'A senha deve ter pelo menos ' . self::MIN_PASSWORD_LENGTH . ' caracteres.';
            } elseif (strlen($password) > self::MAX_PASSWORD_BYTES) {
                $errors['password'] = 'A senha é longa demais.';
            } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                $errors['password'] = 'A senha deve conter letras e números.';
            }

            if (!hash_equals($password, $confirmation)) {
                $errors['password_confirmation'] = 'As senhas não conferem.';
            }

            if (empty($errors['email']) && $this->users->emailExists($email)) {
                $errors['email'] = 'Este e-mail já está cadastrado.';
            }

            if ($errors) {
                return $errors;
            }

            $user = new Users(
                name: $name,
                email: $email,
                active: true,
                password: password_hash($password, PASSWORD_DEFAULT)
            );

            try {
                $this->users->createUser($user);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    return ['email' => 'Este e-mail já está cadastrado.'];
                }
                throw $e;
            }

            return [];
        }

        public function attempt(string $email, string $password, string $ip): Users {
            $email = $this->normalizeEmail($email);

            if ($this->isLockedOut($email, $ip)) {
                throw new AuthException(
                    'Muitas tentativas de login. Tente novamente em ' . self::LOCKOUT_MINUTES . ' minutos.'
                );
            }

            $user = $this->users->findByEmail($email);

            $hash = $user?->password ?? $this->dummyHash();
            $valid = password_verify($password, $hash);

            if (!$user || !$valid || !$user->active) {
                $this->attempts->record($email, $ip);
                throw new AuthException('E-mail ou senha inválidos.');
            }

            if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
                $this->users->updatePassword($user->id, password_hash($password, PASSWORD_DEFAULT));
            }

            $this->attempts->clearForEmail($email);

            Session::regenerate();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['logged_at'] = time();

            return $user;
        }

        public function logout(): void {
            Session::destroy();
        }

        public function user(): ?Users {
            $id = $_SESSION['user_id'] ?? null;

            if (!is_int($id)) {
                return null;
            }

            $user = $this->users->findById($id);

            if (!$user || !$user->active) {
                $this->logout();
                return null;
            }

            return $user;
        }

        public function check(): bool {
            return isset($_SESSION['user_id']);
        }

        private function isLockedOut(string $email, string $ip): bool {
            return $this->attempts->countRecentByEmail($email, self::LOCKOUT_MINUTES) >= self::MAX_ATTEMPTS_PER_EMAIL
                || $this->attempts->countRecentByIp($ip, self::LOCKOUT_MINUTES) >= self::MAX_ATTEMPTS_PER_IP;
        }

        private function normalizeEmail(string $email): string {
            return mb_strtolower(trim($email));
        }

        private function dummyHash(): string {
            static $hash = null;
            return $hash ??= password_hash('dummy-password-timing', PASSWORD_DEFAULT);
        }
    }
