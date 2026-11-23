# StoreFinance

Projeto acadêmico desenvolvido em PHP puro.

## Funcionalidades

- **Autenticação**: cadastro, login e logout, com senhas criptografadas, proteção CSRF e bloqueio temporário após várias tentativas de login erradas.
- **Tarefas**
  - Criar, editar, excluir e filtrar por status (Pendente, Em andamento, Concluída).
  - Botão **Iniciar** para mudar a tarefa de Pendente para Em andamento.
  - **Mini calendário** no dashboard: mostra os prazos de cada dia e, ao clicar em um dia, filtra a lista.
  - Destaque para tarefas atrasadas.
- **Tarefas financeiras**: uma tarefa pode ter uma receita ou despesa prevista (ex.: *pagar imposto de R$ 1.000*). Ao concluí-la, o lançamento é registrado automaticamente. Ao reabri-la, o lançamento é desfeito.
- **Lançamentos**: CRUD de receitas e despesas por categoria, com resumo de entradas, saídas e saldo da loja.

## Como rodar localmente

### 1. Pré-requisitos

- PHP 8.1 ou superior, com a extensão `pdo_mysql`
- MySQL ou MariaDB

### 2. Clonar o projeto

```bash
git clone https://github.com/SEU_USUARIO/financeStore.git
cd financeStore
```

### 3. Criar o banco de dados

```sql
CREATE DATABASE store_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Configurar a conexão

Copie o arquivo de exemplo e preencha com os dados do seu banco:

```bash
cp config/database.example.php config/database.php
```

### 5. Criar as tabelas

```bash
php database/migrate.php up
```

Para desfazer a última leva de migrations:

```bash
php database/migrate.php rollback
```

### 6. Iniciar o servidor

```bash
php -S localhost:8000 -t public public/index.php
```

Acesse **http://localhost:8000** e crie sua conta em *Criar Conta*.

## Estrutura de pastas

```
financeStore/
├── app/
│   ├── Controllers/    # recebem a requisição e escolhem a view
│   ├── Core/           # Router, Controller base, Session, conexão, validação
│   ├── Enums/          # status de tarefa e tipo de lançamento
│   ├── Factories/      # criação de services e da conexão
│   ├── Models/         # entidades (Users, Task, Transaction)
│   ├── Repositories/   # consultas SQL
│   └── Services/       # regras de negócio
├── config/             # configuração do banco (database.php fica fora do Git)
├── database/
│   ├── migrate.php     # executor de migrations
│   └── migrations/
├── public/             # index.php (ponto de entrada) e CSS
├── routes/web.php      # definição das rotas
└── views/              # telas (PHP + HTML)
```

## Conclusão

O StoreFinance foi construído em PHP puro, sem frameworks, seguindo o padrão **MVC** com duas camadas extras: **Services** (regras de negócio) e **Repositories** (acesso ao banco). Cada camada tem uma única responsabilidade, o que deixa o código mais fácil de entender, testar e alterar.

### Fluxo de uma requisição

1. **`public/index.php`** é o único ponto de entrada (*Front Controller*). Ele registra o **autoload** (`spl_autoload_register`), que carrega as classes automaticamente a partir das pastas de `app/`, define cabeçalhos de segurança, cria funções auxiliares para as views (`e()` para escapar HTML, `money()` e `dateBr()` para formatação) e inicia a sessão.
2. **`routes/web.php`** declara as rotas, e o **`Router`** associa o método HTTP e o caminho a um par *Controller + ação*. Rotas inexistentes retornam 404, e métodos errados retornam 405.
3. O **Controller** recebe a requisição, verifica login e token CSRF, chama o Service e escolhe a view ou redireciona.
4. O **Service** valida os dados e aplica as regras de negócio.
5. O **Repository** executa o SQL e transforma cada linha do banco em um objeto **Model**.
6. A **View** exibe o resultado em HTML.

### Estruturas utilizadas

- **Controller base (`app/Core/Controller.php`)**: classe abstrata com o que todos os controllers compartilham: `requireAuth()`, `requireCsrf()`, `render()` e `redirect()`. Depois de todo POST bem-sucedido é feito um redirecionamento (padrão **Post/Redirect/Get**), evitando reenvio do formulário ao atualizar a página. Mensagens de sucesso e erro sobrevivem ao redirecionamento por meio de **flash messages** na sessão.
- **Models (`Users`, `Task`, `Transaction`)**: classes simples com **promoção de propriedades no construtor** e métodos que respondem perguntas sobre o próprio objeto, como `Task::isOverdue()` e `Task::hasFinancial()`.
- **Enums (`TaskStatus`, `TransactionType`)**: *enums* do PHP 8.1 com valor em string. Garantem que só existam status e tipos válidos, e concentram o texto exibido (`label()`) e regras como `canStart()`.
- **Repositories**: todas as consultas usam **PDO com prepared statements**, o que impede SQL Injection. Toda consulta filtra por `user_id`, então um usuário nunca acessa dados de outro. O método `hydrate()` converte o array do banco no Model.
- **Services**: concentram as regras. A validação lança uma `ValidationException` com os erros por campo, que o controller devolve para o formulário. No `TaskService`, concluir uma tarefa financeira cria o lançamento, e reabri-la o exclui. As duas operações rodam dentro de uma **transação do banco** (`beginTransaction`/`commit`/`rollBack`), então nunca fica uma sem a outra.
- **Factories**: o `ServiceFactory` monta cada Service com suas dependências (**injeção de dependência** pelo construtor), e o `DataBaseFactory` mantém uma única conexão PDO por requisição.
- **`CalendarBuilder`**: monta a grade do mini calendário (semanas de domingo a sábado) e agrupa as tarefas pelo dia de vencimento.
- **Migrations**: `database/migrate.php` cria e desfaz as tabelas por versão, permitindo recriar o banco em qualquer máquina.

### Segurança

- Senhas guardadas com `password_hash`, e o hash é atualizado automaticamente quando o algoritmo padrão do PHP muda.
- **Token CSRF** em todos os formulários, comparado com `hash_equals`.
- Sessão com cookie `HttpOnly`, `SameSite=Lax` e ID regenerado no login.
- Bloqueio temporário após várias tentativas de login erradas, por e-mail e por IP.
- Mesmo tempo de resposta para e-mail inexistente e senha errada (`dummyHash`), dificultando descobrir quais e-mails estão cadastrados pelo login.
- Toda saída nas views passa por `e()`, prevenindo XSS.

### Telas e estilo

- As páginas internas usam o layout `views/layouts/header.php`, com barra superior e navegação.
- As telas de login e cadastro compartilham `views/layouts/auth-header.php` e `auth-footer.php`. Cada tela define apenas título, subtítulo e o próprio formulário, sem repetir HTML. O nome do sistema aparece pequeno no topo do card e o título da tela é o `h1`, deixando claro onde o usuário está.
- O CSS usa **variáveis** (`:root`) com a paleta da marca. Os botões seguem um único sistema de classes (`.btn`, `.btn-primary`, `.btn-block`), então o botão laranja das telas de entrada é o mesmo do restante do sistema. As classes das telas de entrada usam o prefixo `auth-`, e o estilo global de `button` define só a fonte e o cursor, sem afetar outros botões.

### Considerações finais

Separar o código em camadas permitiu adicionar funcionalidades, como as tarefas financeiras e o calendário, sem reescrever o que já existia: bastou estender o Model, o Repository e o Service da tarefa. Da mesma forma, a revisão das telas de entrada foi feita apenas nas views e no CSS, sem tocar em regras de negócio. O projeto mostra que, mesmo sem framework, é possível ter um sistema organizado, seguro e de fácil manutenção usando os recursos modernos do PHP: enums, tipagem estrita, PDO e promoção de propriedades.
