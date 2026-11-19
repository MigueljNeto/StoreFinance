<?php
    class TransactionController extends Controller {

        private TransactionService $transactions;

        public function __construct() {
            $this->transactions = ServiceFactory::transactions();
        }

        public function index(): void {
            $user = $this->requireAuth();
            $type = $_GET['type'] ?? null;

            $this->render('transactions/index', [
                'user' => $user,
                'transactions' => $this->transactions->listForUser($user->id, $type),
                'summary' => $this->transactions->summary($user->id),
                'currentType' => TransactionType::tryFrom((string) $type),
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ]);
        }

        public function create(): void {
            $user = $this->requireAuth();

            $this->renderForm($user, null, [
                'type' => TransactionType::EXPENSE->value,
                'transaction_date' => date('Y-m-d'),
            ]);
        }

        public function store(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/transactions/create');

            try {
                $this->transactions->create($user->id, $this->input());
            } catch (ValidationException $e) {
                $this->renderForm($user, null, $this->input(), $e->errors);
                return;
            }

            Session::flash('success', 'Lançamento cadastrado com sucesso!');
            $this->redirect('/transactions');
        }

        public function edit(): void {
            $user = $this->requireAuth();
            $transaction = $this->transactions->find($this->queryId(), $user->id) ?? $this->notFound();

            $this->renderForm($user, $transaction, [
                'description' => $transaction->description,
                'amount' => number_format((float) $transaction->amount, 2, ',', '.'),
                'type' => $transaction->type->value,
                'category' => $transaction->category,
                'transaction_date' => $transaction->transactionDate,
            ]);
        }

        public function update(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/transactions');
            $transaction = $this->transactions->find($this->postId(), $user->id) ?? $this->notFound();

            try {
                $this->transactions->update($transaction, $this->input());
            } catch (ValidationException $e) {
                $this->renderForm($user, $transaction, $this->input(), $e->errors);
                return;
            }

            Session::flash('success', 'Lançamento atualizado com sucesso!');
            $this->redirect('/transactions');
        }

        public function destroy(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/transactions');
            $transaction = $this->transactions->find($this->postId(), $user->id) ?? $this->notFound();

            $this->transactions->delete($transaction);

            Session::flash('success', 'Lançamento excluído.');
            $this->redirect('/transactions');
        }

        private function renderForm(Users $user, ?Transaction $transaction, array $old, array $errors = []): void {
            $this->render('transactions/form', [
                'user' => $user,
                'transaction' => $transaction,
                'old' => $old,
                'errors' => $errors,
                'categories' => TransactionService::CATEGORIES,
            ], $errors ? 422 : 200);
        }
    }
