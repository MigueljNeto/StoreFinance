<?php
    class TaskController extends Controller {

        private TaskService $tasks;

        public function __construct() {
            $this->tasks = ServiceFactory::tasks();
        }

        public function create(): void {
            $user = $this->requireAuth();

            $this->render('tasks/form', [
                'user' => $user,
                'task' => null,
                'old' => [
                    'status' => TaskStatus::PENDING->value,
                    'due_date' => Validator::isDate((string) ($_GET['due_date'] ?? '')) ? $_GET['due_date'] : '',
                ],
                'errors' => [],
            ]);
        }

        public function store(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/tasks/create');

            try {
                $this->tasks->create($user->id, $this->input());
            } catch (ValidationException $e) {
                $this->render('tasks/form', [
                    'user' => $user,
                    'task' => null,
                    'old' => $this->input(),
                    'errors' => $e->errors,
                ], 422);
                return;
            }

            Session::flash('success', 'Tarefa criada com sucesso!');
            $this->redirect('/dashboard');
        }

        public function edit(): void {
            $user = $this->requireAuth();
            $task = $this->tasks->find($this->queryId(), $user->id) ?? $this->notFound();

            $this->render('tasks/form', [
                'user' => $user,
                'task' => $task,
                'old' => [
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status->value,
                    'due_date' => $task->dueDate,
                    'amount' => $task->amount !== null ? number_format((float) $task->amount, 2, ',', '.') : '',
                    'transaction_type' => $task->transactionType?->value,
                    'category' => $task->category,
                ],
                'errors' => [],
            ]);
        }

        public function update(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/dashboard');
            $task = $this->tasks->find($this->postId(), $user->id) ?? $this->notFound();

            try {
                $this->tasks->update($task, $this->input());
            } catch (ValidationException $e) {
                $this->render('tasks/form', [
                    'user' => $user,
                    'task' => $task,
                    'old' => $this->input(),
                    'errors' => $e->errors,
                ], 422);
                return;
            }

            Session::flash('success', 'Tarefa atualizada com sucesso!');
            $this->redirect('/dashboard');
        }

        public function start(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/dashboard');
            $task = $this->tasks->find($this->postId(), $user->id) ?? $this->notFound();

            if ($this->tasks->start($task)) {
                Session::flash('success', 'Tarefa iniciada!');
            } else {
                Session::flash('error', 'Só é possível iniciar tarefas pendentes.');
            }

            $this->redirect('/dashboard');
        }

        public function toggle(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/dashboard');
            $task = $this->tasks->find($this->postId(), $user->id) ?? $this->notFound();

            try {
                $this->tasks->toggle($task);
            } catch (ValidationException $e) {
                Session::flash('error', 'Não foi possível registrar o lançamento: ' . implode(' ', $e->errors));
                $this->redirect('/dashboard');
            }

            if ($task->status === TaskStatus::DONE && $task->isSettled()) {
                Session::flash('success', "Tarefa concluída e {$task->transactionType->label()} de " . money($task->amount) . ' registrada!');
            } elseif ($task->status !== TaskStatus::DONE && $task->hasFinancial()) {
                Session::flash('success', 'Tarefa reaberta. O lançamento gerado por ela foi excluído.');
            }

            $this->redirect('/dashboard');
        }

        public function destroy(): void {
            $user = $this->requireAuth();
            $this->requireCsrf('/dashboard');
            $task = $this->tasks->find($this->postId(), $user->id) ?? $this->notFound();

            $this->tasks->delete($task);

            Session::flash('success', 'Tarefa excluída.');
            $this->redirect('/dashboard');
        }
    }
