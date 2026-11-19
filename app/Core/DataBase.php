<?php
    class DataBase {

    private ?PDO $connection = null;

        public function connect(): PDO {
            $configFile = __DIR__ . '/../../config/database.php';

            if (!is_file($configFile)) {
                throw new RuntimeException('Arquivo config/database.php não encontrado. Copie config/database.example.php e preencha os dados do banco.');
            }

            ['host' => $host, 'dbname' => $dbname, 'username' => $username, 'password' => $password] = require $configFile;

            if ($this->connection instanceof PDO) {
                return $this->connection;
            }

            try {
                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ];

                $this->connection = new PDO($dsn, $username, $password, $options);

                return $this->connection;

            } catch (PDOException $e) {
                throw new PDOException("Erro na conexão com o banco de dados.",(int) $e->getCode(),$e);
            }

        }

    }
