<?php
    class DashboardController extends Controller {

        public function index(): void {
            $user = $this->requireAuth();
            $tasks = ServiceFactory::tasks();
            $status = self::queryString('status');
            $month = self::queryString('month');
            $date = self::queryString('date');
            $selectedDate = $date !== null && Validator::isDate($date) ? $date : null;

            $this->render('dashboard', [
                'user' => $user,
                'tasks' => $tasks->listForUser($user->id, $status, $selectedDate),
                'calendar' => $tasks->calendar($user->id, $month, $selectedDate),
                'selectedDate' => $selectedDate,
                'counts' => $tasks->countByStatus($user->id),
                'summary' => ServiceFactory::transactions()->summary($user->id),
                'currentStatus' => TaskStatus::tryFrom((string) $status),
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ]);
        }

        private static function queryString(string $key): ?string {
            $value = $_GET[$key] ?? null;

            return is_string($value) && $value !== '' ? $value : null;
        }
    }
