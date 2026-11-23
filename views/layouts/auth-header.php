<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | StoreFinance</title>

    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth">

    <main class="auth-container">

        <section class="auth-card">

            <div class="auth-brand">Store<span>Finance</span></div>

            <header class="auth-heading">
                <h1><?= e($heading) ?></h1>
                <p><?= e($subtitle) ?></p>
            </header>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status"><?= e($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>
