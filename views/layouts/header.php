<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'StoreFinance') ?> | StoreFinance</title>

    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="app">

    <header class="topbar">
        <div class="topbar-inner">
            <a href="/dashboard" class="brand">StoreFinance</a>

            <nav class="nav">
                <a href="/dashboard" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">Tarefas</a>
                <a href="/transactions" class="<?= ($active ?? '') === 'transactions' ? 'active' : '' ?>">Lançamentos</a>
            </nav>

            <div class="user-area">
                <span class="user-name"><?= e($user->name) ?></span>
                <form method="POST" action="/logout">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button type="submit" class="btn btn-ghost btn-sm">Sair</button>
                </form>
            </div>
        </div>
    </header>

    <main class="page">

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
