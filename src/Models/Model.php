<?php

use AssessKen\Models\Database;
use PDO;
use Throwable;

abstract class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function create(array $data): int
    {
        try {
            $columns = array_keys($data);
            $this->validateColumns($columns);

            $fields = '`' . implode('`, `', $columns) . '`';
            $values = ':' . implode(', :', $columns);

            $sql = "INSERT INTO `{$this->table}` ($fields)
                    VALUES ($values)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);

            return (int) $this->db->lastInsertId();
        } catch (Throwable $e) {
            error_log((string) $e);
            throw $e;
        }
    }

    public function find(int $id, ?int $schoolId = null): ?array
    {
        try {
            $sql = "SELECT *
                    FROM `{$this->table}`
                    WHERE id = :id";

            $params = ['id' => $id];

            if ($schoolId !== null) {
                $sql .= " AND school_id = :school_id";
                $params['school_id'] = $schoolId;
            }

            $sql .= " LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch() ?: null;
        } catch (Throwable $e) {
            error_log((string) $e);
            throw $e;
        }
    }

    public function all(
        ?int $schoolId = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        try {
            $page = max(1, $page);
            $perPage = max(1, min($perPage, 100));
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT *
                    FROM `{$this->table}`";

            $params = [];

            if ($schoolId !== null) {
                $sql .= " WHERE school_id = :school_id";
                $params['school_id'] = $schoolId;
            }

            $sql .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(":{$key}", $value, PDO::PARAM_INT);
            }

            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log((string) $e);
            throw $e;
        }
    }

    public function update(
        int $id,
        array $data,
        ?int $schoolId = null
    ): bool {
        try {
            unset($data['id']);

            if (empty($data)) {
                return false;
            }

            $columns = array_keys($data);
            $this->validateColumns($columns);

            $sets = [];

            foreach ($columns as $column) {
                $sets[] = "`{$column}` = :{$column}";
            }

            $sql = "UPDATE `{$this->table}`
                    SET " . implode(', ', $sets) . "
                    WHERE id = :id";

            $params = $data;
            $params['id'] = $id;

            if ($schoolId !== null) {
                $sql .= " AND school_id = :school_id";
                $params['school_id'] = $schoolId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            error_log((string) $e);
            throw $e;
        }
    }

    public function delete(
        int $id,
        ?int $schoolId = null
    ): bool {
        try {
            $sql = "DELETE FROM `{$this->table}`
                    WHERE id = :id";

            $params = ['id' => $id];

            if ($schoolId !== null) {
                $sql .= " AND school_id = :school_id";
                $params['school_id'] = $schoolId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            error_log((string) $e);
            throw $e;
        }
    }

    public function count(array $criteria = []): int
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM `{$this->table}`";

            $params = [];
            $conditions = [];

            foreach ($criteria as $column => $value) {
                $this->validateColumn($column);

                $parameter = "p_" . count($params);

                $conditions[] = "`{$column}` = :{$parameter}";
                $params[$parameter] = $value;
            }

            if ($conditions) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log((string) $e);
            throw $e;
        }
    }

    public function search(
        array $columns,
        string $value,
        array $criteria = [],
        int $page = 1,
        int $perPage = 20
    ): array {
        try {
            if (empty($columns)) {
                return [];
            }

            foreach ($columns as $column) {
                $this->validateColumn($column);
            }

            foreach ($criteria as $column => $unused) {
                $this->validateColumn($column);
            }

            $page = max(1, $page);
            $perPage = max(1, min($perPage, 100));
            $offset = ($page - 1) * $perPage;

            $conditions = [];
            $params = [];

            $like = [];

            foreach ($columns as $index => $column) {
                $parameter = "search_{$index}";
                $like[] = "`{$column}` LIKE :{$parameter}";
                $params[$parameter] = "%{$value}%";
            }

            $conditions[] = '(' . implode(' OR ', $like) . ')';

            foreach ($criteria as $column => $criteriaValue) {
                $parameter = "criteria_" . count($params);

                $conditions[] = "`{$column}` = :{$parameter}";
                $params[$parameter] = $criteriaValue;
            }

            $sql = "SELECT *
                    FROM `{$this->table}`
                    WHERE " . implode(' AND ', $conditions) . "
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $param) {
                $stmt->bindValue(":{$key}", $param);
            }

            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log((string) $e);
            throw $e;
        }
    }

    private function validateColumns(array $columns): void
    {
        foreach ($columns as $column) {
            $this->validateColumn($column);
        }
    }

    private function validateColumn(string $column): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new \InvalidArgumentException(
                "Invalid column name: {$column}"
            );
        }
    }
}
