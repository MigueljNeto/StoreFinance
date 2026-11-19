<?php
    abstract class Controller {

        private ?AuthService $auth = null;

        protected function auth(): AuthService {
            return $this->auth ??= ServiceFactory::auth();
        }

        protected function requireAuth(): Users {
            $user = $this->auth()->user();

            if (!$user) {
                Session::flash('error', 'Faça login para continuar.');
                $this->redirect('/login');
            }

            return $user;
        }

        protected function requireCsrf(string $redirectTo): void {
            if (!Session::verifyCsrf($_POST['_csrf'] ?? null)) {
                Session::flash('error', 'Sessão expirada. Tente novamente.');
                $this->redirect($redirectTo);
            }
        }

        protected function render(string $view, array $data = [], int $status = 200): void {
            http_response_code($status);
            extract($data, EXTR_SKIP);
            $csrf = Session::csrfToken();
            require __DIR__ . "/../../views/{$view}.php";
        }

        protected function redirect(string $path): never {
            header("Location: {$path}", true, 303);
            exit;
        }

        protected function notFound(): never {
            http_response_code(404);
            echo 'Registro não encontrado.';
            exit;
        }

        protected function input(): array {
            return array_map(
                fn($value) => is_string($value) ? $value : '',
                $_POST
            );
        }

        protected function queryId(): int {
            return (int) filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 1]]);
        }

        protected function postId(): int {
            return (int) filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 1]]);
        }
    }
