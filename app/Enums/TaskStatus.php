<?php
    enum TaskStatus: string {
        case PENDING = 'PENDING';
        case IN_PROGRESS = 'IN_PROGRESS';
        case DONE = 'DONE';

        public function label(): string {
            return match ($this) {
                self::PENDING => 'Pendente',
                self::IN_PROGRESS => 'Em andamento',
                self::DONE => 'Concluída',
            };
        }

        public function canStart(): bool {
            return $this === self::PENDING;
        }
    }
