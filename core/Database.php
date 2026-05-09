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

        $this->connection = new PDO(
            $dsn,
            $this->config['username'] ?? '',
            $this->config['password'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
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
        $statement->execute($bindings);

        return $statement;
    }

    public function lastInsertId(): string
    {
        return $this->connection()->lastInsertId();
    }
}