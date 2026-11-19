<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | StoreFinance</title>

    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

    <main class="login-container">

        <section class="login-card">

            <header class="login-header">
                <h1>StoreFinance</h1>
            </header>

            <div class="login-content">
                <h2>Bem-vindo!</h2>
                <p>Acesse sua conta.</p>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="status"><?= e($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" role="alert"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="seu@email.com"
                            value="<?= e($old['email'] ?? '') ?>"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Digite sua senha"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button type="submit">Entrar</button>

                </form>

                <p class="register-link">
                    Ainda não possui uma conta?
                    <a href="/register">Criar conta</a>
                </p>
            </div>

        </section>

    </main>

</body>
</html>
