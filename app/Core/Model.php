<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Base Model
 *
 * Provides database access for all models
 */
abstract class Model
{
    protected PDO $db;
    protected App $app;
    protected string $table;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->db();
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all records
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Insert record
     */
    public function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));

        $sql = "UPDATE {$this->table} SET {$sets} WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([...array_values($data), $id]);
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Execute raw query
     */
    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
