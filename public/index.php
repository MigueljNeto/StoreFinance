<?php
    declare(strict_types=1);

    if (PHP_SAPI === 'cli-server') {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $file = realpath(__DIR__ . $path);

        if ($file !== false && is_file($file) && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR)) {
            return false;
        }
    }

    ini_set('display_errors', '0');
    ini_set('log_errors', '1');

    spl_autoload_register(function (string $class): void {
        $folders = ['Core', 'Controllers', 'Services', 'Repositories', 'Models', 'Enums', 'Factories'];

        foreach ($folders as $folder) {
            $file = __DIR__ . "/../app/{$folder}/{$class}.php";

            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    });

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');

    function e(mixed $value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    function money(string|float $value): string {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    function dateBr(?string $value): string {
        return $value ? date('d/m/Y', strtotime($value)) : '—';
    }

    Session::start();

    require_once __DIR__ . '/../routes/web.php';
