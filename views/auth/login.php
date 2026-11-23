<?php
    $title = 'Entrar';
    $heading = 'Bem-vindo de volta';
    $subtitle = 'Acesse sua conta para continuar.';
    require __DIR__ . '/../layouts/auth-header.php';
?>

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

                <button type="submit" class="btn btn-primary btn-block">Entrar</button>

            </form>

            <p class="auth-switch">
                Ainda não possui uma conta?
                <a href="/register">Criar conta</a>
            </p>

        </section>

    </main>

</body>
</html>
