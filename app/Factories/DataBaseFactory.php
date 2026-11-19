<?php
    class DataBaseFactory {

        private static ?PDO $connection = null;

        public static function create(): PDO {
            if (self::$connection === null) {
                $dataBase = new DataBase();
                self::$connection = $dataBase->connect();
            }

            return self::$connection;
        }
    }
