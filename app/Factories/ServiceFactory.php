<?php
    class ServiceFactory {

        public static function auth(): AuthService {
            $pdo = DataBaseFactory::create();

            return new AuthService(
                new UserRepository($pdo),
                new LoginAttemptRepository($pdo)
            );
        }

        public static function tasks(): TaskService {
            $pdo = DataBaseFactory::create();

            return new TaskService(new TaskRepository($pdo), self::transactions(), $pdo);
        }

        public static function transactions(): TransactionService {
            return new TransactionService(new TransactionRepository(DataBaseFactory::create()));
        }
    }
