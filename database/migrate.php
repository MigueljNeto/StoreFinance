<?php

require_once __DIR__ . '/../app/Core/DataBase.php';
require_once __DIR__ . '/../app/Factories/DataBaseFactory.php';

$pdo = DataBaseFactory::create();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT UNSIGNED NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$command = $argv[1] ?? 'up';

if ($command === 'up') {
    migrate($pdo);
} elseif ($command === 'rollback') {
    rollback($pdo);
} else {
    echo "Comando inválido. Use: php database/migrate.php [up|rollback]" . PHP_EOL;
    exit(1);
}

function migrate(PDO $pdo): void {
    $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

    $files = glob(__DIR__ . '/migrations/*.php');
    sort($files);

    $pending = array_filter($files, fn($file) => !in_array(basename($file, '.php'), $executed));

    if (empty($pending)) {
        echo "Nada para migrar." . PHP_EOL;
        return;
    }

    $batch = (int) $pdo->query("SELECT COALESCE(MAX(batch), 0) FROM migrations")->fetchColumn() + 1;

    $insert = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");

    foreach ($pending as $file) {
        $name = basename($file, '.php');
        $migration = require $file;

        $migration->up($pdo);
        $insert->execute(['migration' => $name, 'batch' => $batch]);

        echo "Migrado: {$name}" . PHP_EOL;
    }
}

function rollback(PDO $pdo): void {
    $batch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();

    if ($batch === null) {
        echo "Nada para reverter." . PHP_EOL;
        return;
    }

    $select = $pdo->prepare("SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC");
    $select->execute(['batch' => $batch]);

    $delete = $pdo->prepare("DELETE FROM migrations WHERE migration = :migration");

    foreach ($select->fetchAll(PDO::FETCH_COLUMN) as $name) {
        $migration = require __DIR__ . "/migrations/{$name}.php";

        $migration->down($pdo);
        $delete->execute(['migration' => $name]);

        echo "Revertido: {$name}" . PHP_EOL;
    }
}
