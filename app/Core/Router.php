<?php
    class Router {

        private array $routes = [];

        public function get(string $path, string $controller, string $action): void {
            $this->routes['GET'][$path] = [$controller, $action];
        }

        public function post(string $path, string $controller, string $action): void {
            $this->routes['POST'][$path] = [$controller, $action];
        }

        public function dispatch(string $method, string $uri): void {
            $path = rtrim((string) parse_url($uri, PHP_URL_PATH), '/') ?: '/';

            $route = $this->routes[$method][$path] ?? null;

            if ($route === null) {
                $allowed = array_keys(array_filter($this->routes, fn($r) => isset($r[$path])));

                if ($allowed) {
                    http_response_code(405);
                    header('Allow: ' . implode(', ', $allowed));
                    echo 'Método não permitido.';
                    return;
                }

                http_response_code(404);
                echo 'Página não encontrada.';
                return;
            }

            [$controller, $action] = $route;
            (new $controller())->$action();
        }
    }
