<?php
    class CalendarBuilder {

        private const MONTHS = [
            1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
        ];

        public const WEEKDAYS = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

        public static function gridStart(DateTimeImmutable $month): DateTimeImmutable {
            $first = $month->modify('first day of this month');
            $weekday = (int) $first->format('w');

            return $first->modify("-{$weekday} days");
        }

        public static function gridEnd(DateTimeImmutable $month): DateTimeImmutable {
            $last = $month->modify('last day of this month');
            $weekday = (int) $last->format('w');

            return $last->modify('+' . (6 - $weekday) . ' days');
        }

        public static function monthLabel(DateTimeImmutable $month): string {
            return self::MONTHS[(int) $month->format('n')] . ' de ' . $month->format('Y');
        }

        public function build(DateTimeImmutable $month, array $tasks): array {
            $byDate = [];
            foreach ($tasks as $task) {
                if ($task->dueDate !== null) {
                    $byDate[$task->dueDate][] = $task;
                }
            }

            $currentMonth = $month->format('Y-m');
            $today = date('Y-m-d');
            $end = self::gridEnd($month);

            $weeks = [];
            $week = [];

            for ($day = self::gridStart($month); $day <= $end; $day = $day->modify('+1 day')) {
                $date = $day->format('Y-m-d');

                $week[] = [
                    'date' => $date,
                    'day' => (int) $day->format('j'),
                    'inMonth' => $day->format('Y-m') === $currentMonth,
                    'isToday' => $date === $today,
                    'tasks' => $byDate[$date] ?? [],
                ];

                if (count($week) === 7) {
                    $weeks[] = $week;
                    $week = [];
                }
            }

            return $weeks;
        }
    }
