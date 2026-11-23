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
