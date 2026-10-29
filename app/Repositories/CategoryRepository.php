<?php

declare(strict_types=1);

class CategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY type ASC, name ASC')->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, type, description) VALUES (?, ?, ?)'
        );
        $stmt->execute([$data['name'], $data['type'], $data['description']]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categories SET name = ?, type = ?, description = ? WHERE id = ?'
        );
        return $stmt->execute([$data['name'], $data['type'], $data['description'], $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
