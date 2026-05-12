<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOStatement;

final class Database
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly array $config
    ) {
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['driver'] ?? 'mysql',
            $this->config['host'] ?? '127.0.0.1',
            $this->config['port'] ?? 3306,
            $this->config['database'] ?? '',
            $this->config['charset'] ?? 'utf8mb4'
        );

        $pdoOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // 2. Only add SSL if the CA path is provided
        $caPath = $this->config['ssl_ca'] ?? null;
        if ($caPath) {
            $pdoOptions[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
            $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        $this->connection = new PDO(
            $dsn,
            $this->config['username'] ?? '',
            $this->config['password'] ?? '',
            $pdoOptions
        );

        return $this->connection;
    }

    public function beginTransaction(): bool
    {
        return $this->connection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection()->commit();
    }

    public function rollBack(): bool
    {
        if (!$this->connection()->inTransaction()) {
            return false;
        }

        return $this->connection()->rollBack();
    }

    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $statement = $this->connection()->prepare($sql);

        foreach ($bindings as $key => $value) {
            // Determine the PDO type (default to string)
            $type = PDO::PARAM_STR;
            if (is_numeric($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $type = PDO::PARAM_NULL;
            }

            // Check if key is numeric (0, 1, 2) or named (:id, :limit)
            $param = is_numeric($key) ? $key + 1 : $key;
            $statement->bindValue($param, $value, $type);
        }
        
        $statement->execute($bindings);
        return $statement;
    }

    public function lastInsertId(): string
    {
        return $this->connection()->lastInsertId();
    }
}