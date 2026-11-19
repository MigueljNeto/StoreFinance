<?php

    class AuthController extends Controller {

        public function login(): void {
            if ($this->auth()->check()) {
                $this->redirect('/dashboard');
            }

            $this->render('auth/login', [
                'error' => Session::getFlash('error'),
                'success' => Session::getFlash('success'),
                'old' => Session::getFlash('old', []),
            ]);
        }


        public function authenticate(): void {
            $this->requireCsrf('/login');

            $email = (string) ($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            try {
                $this->auth()->attempt($email, $password, $this->clientIp());
            } catch (AuthException $e) {
                Session::flash('error', $e->getMessage());
                Session::flash('old', ['email' => $email]);
                $this->redirect('/login');
            }

            $this->redirect('/dashboard');
        }

        public function register(): void {
            if ($this->auth()->check()) {
                $this->redirect('/dashboard');
            }

            $this->render('auth/register', [
                'error' => Session::getFlash('error'),
                'errors' => Session::getFlash('errors', []),
                'old' => Session::getFlash('old', []),
            ]);
        }

        public function store(): void {
            $this->requireCsrf('/register');

            $name = (string) ($_POST['name'] ?? '');
            $email = (string) ($_POST['email'] ?? '');

            $errors = $this->auth()->register(
                $name,
                $email,
                (string) ($_POST['password'] ?? ''),
                (string) ($_POST['password_confirmation'] ?? '')
            );

            if ($errors) {
                Session::flash('errors', $errors);
                Session::flash('old', ['name' => $name, 'email' => $email]);
                $this->redirect('/register');
            }

            Session::flash('success', 'Conta criada com sucesso! Faça login.');
            $this->redirect('/login');
        }

        public function logout(): void {
            $this->requireCsrf('/dashboard');
            $this->auth()->logout();
            $this->redirect('/login');
        }

        private function clientIp(): string {
            return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        }
    }
