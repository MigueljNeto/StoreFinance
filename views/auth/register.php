<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | StoreFinance</title>

    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

    <main class="login-container">

        <section class="login-card">

            <header class="login-header">
                <h1>StoreFinance</h1>
            </header>

            <div class="login-content">
                <h2>Cadastre sua conta!</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" role="alert"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/register">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Seu Nome"
                            value="<?= e($old['name'] ?? '') ?>"
                            maxlength="120"
                            autocomplete="name"
                            required
                        >
                        <?php if (!empty($errors['name'])): ?>
                            <small class="field-error"><?= e($errors['name']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="seu@email.com"
                            value="<?= e($old['email'] ?? '') ?>"
                            maxlength="150"
                            autocomplete="email"
                            required
                        >
                        <?php if (!empty($errors['email'])): ?>
                            <small class="field-error"><?= e($errors['email']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Mínimo 8 caracteres, letras e números"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                        <?php if (!empty($errors['password'])): ?>
                            <small class="field-error"><?= e($errors['password']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Digite Novamente</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Digite sua senha novamente"
                            autocomplete="new-password"
                            required
                        >
                        <?php if (!empty($errors['password_confirmation'])): ?>
                            <small class="field-error"><?= e($errors['password_confirmation']) ?></small>
                        <?php endif; ?>
                    </div>

                    <button type="submit">Cadastrar</button>

                </form>

                <p class="register-link">
                    Já possui uma conta?
                    <a href="/login">Entrar</a>
                </p>

            </div>

        </section>

    </main>

</body>
</html>
