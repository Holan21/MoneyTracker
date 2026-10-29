<?php

declare(strict_types=1);

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findByLogin(string $login): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE login = ? LIMIT 1');
        $stmt->execute([$login]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (login, password_hash, name, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['login'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['name'],
            $data['role'] ?? 'user',
        ]);
        return (int)$this->db->lastInsertId();
    }
}
