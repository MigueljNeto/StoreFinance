<?php
    class Session {

        public static function start(): void {
            if (session_status() === PHP_SESSION_ACTIVE) {
                return;
            }

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_name('FINANCESTORE_SESSID');
            session_start();
        }

        public static function regenerate(): void {
            session_regenerate_id(true);
        }

        public static function destroy(): void {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'],
                ]);
            }

            session_destroy();
        }

        public static function csrfToken(): string {
            if (empty($_SESSION['_csrf'])) {
                $_SESSION['_csrf'] = bin2hex(random_bytes(32));
            }

            return $_SESSION['_csrf'];
        }

        public static function verifyCsrf(?string $token): bool {
            return is_string($token)
                && !empty($_SESSION['_csrf'])
                && hash_equals($_SESSION['_csrf'], $token);
        }

        public static function flash(string $key, mixed $value): void {
            $_SESSION['_flash'][$key] = $value;
        }

        public static function getFlash(string $key, mixed $default = null): mixed {
            $value = $_SESSION['_flash'][$key] ?? $default;
            unset($_SESSION['_flash'][$key]);

            return $value;
        }
    }
