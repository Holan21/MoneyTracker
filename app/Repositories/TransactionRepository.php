<?php

declare(strict_types=1);

class TransactionRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function all(): array
    {
        $sql = 'SELECT t.*, c.name AS category_name
                FROM transactions t
                INNER JOIN categories c ON c.id = t.category_id
                ORDER BY t.occurred_at DESC, t.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, c.name AS category_name
             FROM transactions t
             INNER JOIN categories c ON c.id = t.category_id
             WHERE t.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function byCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, c.name AS category_name
             FROM transactions t
             INNER JOIN categories c ON c.id = t.category_id
             WHERE t.category_id = ?
             ORDER BY t.occurred_at DESC, t.id DESC'
        );
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions (category_id, title, amount, type, occurred_at, receipt)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['category_id'],
            $data['title'],
            $data['amount'],
            $data['type'],
            $data['occurred_at'],
            $data['receipt'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE transactions
             SET category_id = ?, title = ?, amount = ?, type = ?, occurred_at = ?, receipt = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            $data['category_id'],
            $data['title'],
            $data['amount'],
            $data['type'],
            $data['occurred_at'],
            $data['receipt'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM transactions WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
